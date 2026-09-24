<?php

declare(strict_types=1);

/**
 * This file is part of the Phalcon Developer Tools.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Phalcon\DevTools\Builder\Component;

use Phalcon\Db\Adapter\Pdo\AbstractPdo;
use Phalcon\Db\Column;
use Phalcon\Db\ReferenceInterface;
use Phalcon\DevTools\Builder\Exception\BuilderException;
use Phalcon\DevTools\Exception\InvalidArgumentException;
use Phalcon\DevTools\Exception\InvalidParameterException;
use Phalcon\DevTools\Exception\RuntimeException;
use Phalcon\DevTools\Exception\WriteFileException;
use Phalcon\DevTools\Generator\Snippet;
use Phalcon\DevTools\Options\OptionsAware as ModelOption;
use Phalcon\DevTools\Utils;
use Phalcon\Support\HelperFactory;
use Phalcon\Filter\Validation;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionProperty;

/**
 * Builder to generate models
 */
class Model extends AbstractComponent
{
    /**
     * Map of scalar data objects
     *
     * @var array
     */
    private $typeMap = [
        //'Date' => 'Date',
        //'Decimal' => 'Decimal'
    ];

    /**
     * Options container
     *
     * @var ModelOption
     */
    protected $modelOptions;

    /**
     * Create Builder object
     *
     * @param array $options
     * @throws InvalidArgumentException
     */
    public function __construct(array $options)
    {
        $this->modelOptions = new ModelOption($options);

        if (!$this->modelOptions->hasOption('name')) {
            throw new InvalidArgumentException('Please, specify the table name');
        }

        $this->modelOptions->setNotDefinedOption('camelize', false);
        $this->modelOptions->setNotDefinedOption('force', false);
        $this->modelOptions->setNotDefinedOption(
            'className',
            Utils::lowerCamelizeWithDelimiter($options['name'], '_-')
        );
        $this->modelOptions->setNotDefinedOption('fileName', Utils::lowerCamelizeWithDelimiter($options['name'], '_-'));
        $this->modelOptions->setNotDefinedOption('abstract', false);
        $this->modelOptions->setNotDefinedOption('annotate', false);

        if ($this->modelOptions->getOption('abstract')) {
            $this->modelOptions->setOption('className', 'Abstract' . $this->modelOptions->getOption('className'));
        }

        parent::__construct($options);

        $this->modelOptions->setOption('config', $this->modelOptions->getOption('config'));
        $this->modelOptions->setOption('snippet', new Snippet());
    }

    /**
     * Module build
     *
     * @throws BuilderException
     */
    public function build(): void
    {
        $config = $this->modelOptions->getOption('config');
        $snippet = $this->modelOptions->getOption('snippet');

        if ($this->modelOptions->hasOption('directory')) {
            $this->path->setRootPath($this->modelOptions->getOption('directory'));
        }

        $helper = new HelperFactory();
        $methodRawCode = [];
        $this->setModelsDir();
        $this->setModelPath();

        $modelPath = $this->modelOptions->getOption('modelPath');

        $this->checkDataBaseParam();

        if (isset($config->devtools->loader)) {
            /** @noinspection PhpIncludeInspection */
            require_once $config->devtools->loader;
        }

        $namespace = $this->modelOptions->hasOption('namespace')
            ? (string) $this->modelOptions->getOption('namespace') : '';

        if ($this->checkNamespace($namespace) && !empty(trim($namespace))) {
            $namespace = 'namespace ' . $this->modelOptions->getOption('namespace') . ';' . PHP_EOL . PHP_EOL;
        }

        $genDocMethods = $this->modelOptions->getValidOptionOrDefault('genDocMethods', false);
        $useSettersGetters = $this->modelOptions->getValidOptionOrDefault('genSettersGetters', false);

        $adapter = $config->database->adapter ?? 'Mysql';
        $this->isSupportedAdapter($adapter);

        if (is_object($config->database)) {
            $configArray = $config->database->toArray();
        } else {
            $configArray = $config->database;
        }

        // An array for use statements
        $uses = [];

        $adapterName = 'Phalcon\Db\Adapter\Pdo\\' . $adapter;
        unset($configArray['adapter']);

        /** @var AbstractPdo $db */
        $db = new $adapterName($configArray);

        $initialize = [];

        if ($this->modelOptions->hasOption('schema')) {
            $schema = $this->modelOptions->getOption('schema');
        } else {
            $schema = Utils::resolveDbSchema($config->database);
        }

        if ($schema) {
            $initialize['schema'] = $snippet->getThisMethod('setSchema', $schema);
        }
        $initialize['source'] = $snippet->getThisMethod('setSource', $this->modelOptions->getOption('name'));

        $table = $this->modelOptions->getOption('name');

        if (!$db->tableExists($table, $schema)) {
            throw new InvalidArgumentException(sprintf('Table "%s" does not exist.', $table));
        }

        $fields = $db->describeColumns($table, $schema);
        $referenceList = $this->getReferenceList($schema, $db);
        $relations = [];

        /**
         * Reverse relations: other tables reference this model.
         */
        foreach ($referenceList as $tableName => $references) {
            foreach ($references as $reference) {
                if ($reference->getReferencedTable() !== $this->modelOptions->getOption('name')) {
                    continue;
                }

                $entityNamespace = $this->modelOptions->hasOption('namespace')
                    ? $this->modelOptions->getOption('namespace') . '\\'
                    : '';

                $refColumns = $reference->getReferencedColumns();
                $columns    = $reference->getColumns();

                $baseAlias = $helper->camelize($tableName, '_-');

                $relations[] = [
                    'type'          => 'hasMany',
                    'field'         => $this->getFieldName($refColumns[0]),
                    'entity'        => $entityNamespace . $helper->camelize($tableName, '_-'),
                    'relatedField'  => $this->getFieldName($columns[0]),
                    'baseAlias'     => $baseAlias,

                    // FK from the referencing table.
                    // Example: games.away_team_id
                    'roleField'     => $columns[0],

                    // The FK points to the current table.
                    // Example: teams
                    'roleTable'     => $this->modelOptions->getOption('name'),
                ];
            }
        }

        /**
         * Direct relations: this model references other tables.
         */
        foreach ($db->describeReferences(
            $this->modelOptions->getOption('name'),
            $schema
        ) as $reference
        ) {
            $entityNamespace = $this->modelOptions->hasOption('namespace')
                ? $this->modelOptions->getOption('namespace')
                : '';

            $refColumns = $reference->getReferencedColumns();
            $columns    = $reference->getColumns();

            $referencedTable = $reference->getReferencedTable();
            $baseAlias       = $helper->camelize($referencedTable, '_-');

            $relations[] = [
                'type'          => 'belongsTo',
                'field'         => $this->getFieldName($columns[0]),
                'entity'        => $this->getEntityClassName(
                    $reference,
                    $entityNamespace
                ),
                'relatedField'  => $this->getFieldName($refColumns[0]),
                'baseAlias'     => $baseAlias,

                // FK from the current table.
                // Example: games.home_team_id
                'roleField'     => $columns[0],

                // The FK points to this table.
                // Example: teams
                'roleTable'     => $referencedTable,
            ];
        }

        /**
         * Resolve duplicate aliases before generating initialize().
         */
        $relations = $this->resolveRelationAliases($relations);

        foreach ($relations as $relation) {
            $initialize[] = $snippet->getRelation(
                $relation['type'],
                $relation['field'],
                $relation['entity'],
                $relation['relatedField'],
                "['alias' => '" . $relation['alias'] . "']"
            );
        }

        $alreadyInitialized  = false;
        $alreadyValidations  = false;
        $alreadyColumnMapped = false;
        $attributes          = [];

        if (file_exists($modelPath)) {
            try {
                $possibleMethods = [];
                if ($useSettersGetters) {
                    foreach ($fields as $field) {
                        /** @var \Phalcon\Db\Column $field */
                        $methodName = $helper->camelize($field->getName(), '_-');

                        $possibleMethods['set' . $methodName] = true;
                        $possibleMethods['get' . $methodName] = true;
                    }
                }

                /** @noinspection PhpIncludeInspection */
                require_once $modelPath;

                $linesCode = file($modelPath);
                $fullClassName = $this->modelOptions->getOption('className');
                if ($this->modelOptions->hasOption('namespace')) {
                    $fullClassName = $this->modelOptions->getOption('namespace') . '\\' . $fullClassName;
                }
                $reflection = new ReflectionClass($fullClassName);
                foreach ($reflection->getMethods() as $method) {
                    if ($method->getDeclaringClass()->getName() !== $fullClassName) {
                        continue;
                    }

                    $methodName = $method->getName();
                    if (isset($possibleMethods[$methodName])) {
                        continue;
                    }

                    $indent = PHP_EOL;
                    if ($method->getDocComment()) {
                        $firstLine = $linesCode[$method->getStartLine() - 1];
                        preg_match('#^\s+#', $firstLine, $matches);
                        if (isset($matches[0])) {
                            $indent .= $matches[0];
                        }
                    }

                    $methodDeclaration = join(
                        '',
                        array_slice(
                            $linesCode,
                            $method->getStartLine() - 1,
                            $method->getEndLine() - $method->getStartLine() + 1
                        )
                    );

                    $methodRawCode[$methodName] = $indent . $method->getDocComment() . PHP_EOL . $methodDeclaration;

                    switch ($methodName) {
                        case 'initialize':
                            $alreadyInitialized = true;
                            break;
                        case 'validation':
                            $alreadyValidations = true;
                            break;
                        case 'columnMap':
                            $alreadyColumnMapped = true;
                            break;
                    }
                }

                $possibleFieldsTransformed = [];
                foreach ($fields as $field) {
                    $fieldName = $this->getFieldName($field->getName());
                    $possibleFieldsTransformed[$fieldName] = true;
                }

                if (method_exists($reflection, 'getReflectionConstants')) {
                    foreach ($reflection->getReflectionConstants() as $constant) {
                        if ($constant->getDeclaringClass()->getName() !== $fullClassName) {
                            continue;
                        }

                        $constantsPreg = '/const(\s+)' . $constant->getName() . '([\s=;]+)/';
                        $attribute = $this->getAttribute($linesCode, $constantsPreg, $constant);
                        if (!empty($attribute)) {
                            $attributes[] = $attribute;
                        }
                    }
                }

                foreach ($reflection->getProperties() as $property) {
                    $propertyName = $property->getName();
                    if (!empty($possibleFieldsTransformed[$propertyName])
                        || $property->getDeclaringClass()->getName() !== $fullClassName
                    ) {
                        continue;
                    }

                    $modifiersPreg = '';
                    switch ($property->getModifiers()) {
                        case ReflectionProperty::IS_PUBLIC:
                            $modifiersPreg = '^(\s*)public(\s+)';
                            break;
                        case ReflectionProperty::IS_PRIVATE:
                            $modifiersPreg = '^(\s*)private(\s+)';
                            break;
                        case ReflectionProperty::IS_PROTECTED:
                            $modifiersPreg = '^(\s*)protected(\s+)';
                            break;
                        case ReflectionProperty::IS_STATIC + ReflectionProperty::IS_PUBLIC:
                            $modifiersPreg = '^(\s*)(public?)(\s+)static(\s+)';
                            break;
                        case ReflectionProperty::IS_STATIC + ReflectionProperty::IS_PROTECTED:
                            $modifiersPreg = '^(\s*)protected(\s+)static(\s+)';
                            break;
                        case ReflectionProperty::IS_STATIC + ReflectionProperty::IS_PRIVATE:
                            $modifiersPreg = '^(\s*)private(\s+)static(\s+)';
                            break;
                    }

                    $modifiersPreg = '/' . $modifiersPreg . '\$' . $propertyName . '([\s=;]+)/';
                    $attribute = $this->getAttribute($linesCode, $modifiersPreg, $property);
                    if (!empty($attribute)) {
                        $attributes[] = $attribute;
                    }
                }
            } catch (\Exception $e) {
                throw new RuntimeException(
                    sprintf(
                        'Failed to create the model "%s". Error: %s',
                        $this->modelOptions->getOption('className'),
                        $e->getMessage()
                    )
                );
            }
        }

        $validations = [];
        foreach ($fields as $field) {
            $fieldName = $this->getFieldName($field->getName());

            if ($field->getType() === Column::TYPE_CHAR) {
                $domain = [];
                if (preg_match('/\((.*)\)/', (string)$field->getType(), $matches)) {
                    foreach (explode(',', $matches[1]) as $item) {
                        $domain[] = $item;
                    }
                }
                if (count($domain)) {
                    $varItems = join(', ', $domain);
                    $validations[] = $snippet->getValidateInclusion($fieldName, $varItems);
                }
            }
        }

        if (count($validations)) {
            $validations[] = $snippet->getValidationEnd();
        }

        // Check if there has been an extender class
        $extends = $this->modelOptions->getValidOptionOrDefault('extends', '\Phalcon\Mvc\Model');

        // Check if there have been any excluded fields
        $exclude = [];
        if ($this->modelOptions->hasOption('excludeFields')) {
            $keys = explode(',', $this->modelOptions->getOption('excludeFields'));
            if (count($keys) > 0) {
                foreach ($keys as $key) {
                    $exclude[trim($key)] = '';
                }
            }
        }

        $setters = [];
        $getters = [];
        foreach ($fields as $field) {
            if (array_key_exists(strtolower($field->getName()), $exclude)) {
                continue;
            }

            $type      = $this->getPHPType($field->getType());
            $docType   = $field->isNotNull() ? $type : $type . '|null';
            $fieldName = $this->getFieldName($field->getName());

            $attributes[] = $snippet->getAttributes(
                $type,
                $useSettersGetters ? 'protected' : 'public',
                $field,
                $this->modelOptions->getOption('annotate'),
                $fieldName
            );

            if ($useSettersGetters) {
                $methodName = Utils::camelize($field->getName(), '_-');

                $setters[] = $snippet->getSetter(
                    $field->getName(),
                    $fieldName,
                    $docType,
                    $methodName
                );

                if (isset($this->typeMap[$type])) {
                    $getters[] = $snippet->getGetterMap(
                        $fieldName,
                        $docType,
                        $methodName,
                        $this->typeMap[$type]
                    );
                } else {
                    $getters[] = $snippet->getGetter(
                        $fieldName,
                        $docType,
                        $methodName
                    );
                }
            }
        }

        $validationsCode = '';
        if (!$alreadyValidations && count($validations) > 0) {
            $validationsCode = $snippet->getValidationsMethod($validations);
            $uses[] = $snippet->getUse(Validation::class);
        }

        $initCode = '';
        if (!$alreadyInitialized && count($initialize) > 0) {
            $initCode = $snippet->getInitialize($initialize);
        }

        $content = join('', $attributes);

        if ($useSettersGetters) {
            $content .= join('', $setters) . join('', $getters);
        }

        $content .= $validationsCode . $initCode;
        foreach ($methodRawCode as $methodCode) {
            $content .= $methodCode;
        }

        $classDoc = $snippet->getClassDoc(
            $this->modelOptions->getOption('className'),
            $namespace,
            $extends,
            $genDocMethods
        );

        if ($this->modelOptions->hasOption('mapColumn') &&
            $this->modelOptions->getOption('mapColumn') &&
            !$alreadyColumnMapped
        ) {
            $content .= $snippet->getColumnMap($fields, $this->modelOptions->getOption('camelize'));
        }

        $useDefinition = '';
        if (!empty($uses)) {
            usort($uses, function ($a, $b) {
                return strlen($a) - strlen($b);
            });

            $useDefinition = join("\n", $uses) . PHP_EOL . PHP_EOL;
        }

        $abstract = ($this->modelOptions->getOption('abstract') ? 'abstract ' : '');

        $code = $snippet->getClass(
            $namespace,
            $useDefinition,
            $this->modelOptions,
            $content,
            $classDoc,
            $abstract,
            $extends
        );

        if (file_exists($modelPath) && !is_writable($modelPath)) {
            throw new WriteFileException(sprintf('Unable to write to %s. Check write-access of a file.', $modelPath));
        }

        if (!file_put_contents($modelPath, $code)) {
            throw new WriteFileException(sprintf('Unable to write to %s', $modelPath));
        }

        if ($this->isConsole()) {
            $msgSuccess = ($this->modelOptions->getOption('abstract') ? 'Abstract ' : '');
            $msgSuccess .= 'Model "%s" was successfully created.';
            $this->notifySuccess(sprintf($msgSuccess, $this->modelOptions->getOption('className')));
        }
    }

    /**
     * @param array $linesCode
     * @param string $pattern
     * @param ReflectionProperty|ReflectionClassConstant $attribute
     *
     * @return null|string
     */
    protected function getAttribute(array $linesCode, string $pattern, $attribute): ?string
    {
        $endLine = $startLine = 0;
        foreach ($linesCode as $line => $code) {
            if (preg_match($pattern, $code)) {
                $startLine = $line;
                break;
            }
        }
        if (!empty($startLine)) {
            $countLines = count($linesCode);
            for ($i = $startLine; $i < $countLines; $i++) {
                if (preg_match('/;(\s*)$/', $linesCode[$i])) {
                    $endLine = $i;
                    break;
                }
            }
        }

        if (!empty($startLine) && !empty($endLine)) {
            $attributeDeclaration = join(
                '',
                array_slice(
                    $linesCode,
                    $startLine,
                    $endLine - $startLine + 1
                )
            );
            $attributeFormatted = $attributeDeclaration;
            if (!empty($attribute->getDocComment())) {
                $attributeFormatted = "    " . $attribute->getDocComment() . PHP_EOL . $attribute;
            }
            return $attributeFormatted;
        }

        return null;
    }

    /**
     * @param string $fieldName
     *
     * @return string
     */
    protected function getFieldName(string $fieldName): string
    {
        if ($this->modelOptions->getOption('camelize')) {
            return Utils::lowerCamelize(Utils::camelize($fieldName, '_-'));
        }

        return Utils::lowerCamelizeWithDelimiter($fieldName, '-', true);
    }

    /**
     * Set path to model
     *
     * @throw WriteFileException
     */
    protected function setModelPath(): void
    {
        $modelPath = $this->modelOptions->getOption('modelsDir');

        if (!$this->isAbsolutePath($modelPath)) {
            $modelPath = $this->path->getRootPath($modelPath);
        }

        $modelPath .= $this->modelOptions->getOption('className') . '.php';

        if (file_exists($modelPath) && !$this->modelOptions->getOption('force')) {
            throw new WriteFileException(sprintf(
                'The model file "%s.php" already exists in models dir',
                $this->modelOptions->getOption('className')
            ));
        }

        $this->modelOptions->setOption('modelPath', $modelPath);
    }

    /**
     * @throw InvalidParameterException
     */
    protected function checkDataBaseParam(): void
    {
        if (!isset($this->modelOptions->getOption('config')->database)) {
            throw new InvalidParameterException('Database configuration cannot be loaded from your config file.');
        }

        if (!isset($this->modelOptions->getOption('config')->database->adapter)) {
            throw new InvalidParameterException(
                "Adapter was not found in the config. " .
                    "Please specify a config variable [database][adapter]"
            );
        }
    }

    /**
     * Resolve relation aliases while preserving the historical DevTools alias
     * whenever a canonical relation exists.
     *
     * Examples:
     *
     * team_id
     *     => Teams
     *
     * home_team_id
     * away_team_id
     *     => TeamsHome
     *     => TeamsAway
     *
     * user_id
     * user_id_updated
     *     => Users
     *     => UsersUpdated
     *
     * @param array $relations
     *
     * @return array
     */
    protected function resolveRelationAliases(array $relations): array
    {
        $counts = [];

        foreach ($relations as $relation) {
            $baseAlias = $relation['baseAlias'];

            $counts[$baseAlias] = ($counts[$baseAlias] ?? 0) + 1;
        }

        $usedAliases = [];

        foreach ($relations as $index => $relation) {
            $baseAlias = $relation['baseAlias'];

            /**
             * No collision: preserve the historical DevTools alias.
             */
            if ($counts[$baseAlias] === 1) {
                $alias = $baseAlias;
            } else {
                $role = $this->getRelationRole(
                    $relation['roleField'],
                    $relation['roleTable']
                );

                /**
                 * A relation without a semantic role is considered the
                 * canonical relation and keeps the original alias.
                 *
                 * user_id => Users
                 *
                 * Additional relations receive a semantic suffix:
                 *
                 * user_id_updated => UsersUpdated
                 */
                $alias = $role !== ''
                    ? $baseAlias . $role
                    : $baseAlias;
            }

            /**
             * Last-resort protection against collisions.
             *
             * This should normally only happen when the database schema
             * does not provide enough semantic information to distinguish
             * two relations.
             */
            $originalAlias = $alias;
            $suffix = 2;

            while (isset($usedAliases[$alias])) {
                $alias = $originalAlias . $suffix;
                $suffix++;
            }

            $usedAliases[$alias] = true;
            $relations[$index]['alias'] = $alias;
        }

        return $relations;
    }

    /**
     * @param ReferenceInterface $reference
     * @param string $namespace
     * @return string
     */
    protected function getEntityClassName(ReferenceInterface $reference, string $namespace): string
    {
        $referencedTable = Utils::camelize($reference->getReferencedTable());

        return "{$namespace}\\{$referencedTable}";
    }

    /**
     * Get reference list from option
     *
     * @param string $schema
     * @param AbstractPdo $db
     * @return array
     */
    protected function getReferenceList(?string $schema, AbstractPdo $db): array
    {
        if ($this->modelOptions->hasOption('referenceList')) {
            return $this->modelOptions->getOption('referenceList');
        }

        $referenceList = [];
        foreach ($db->listTables($schema) as $name) {
            $referenceList[$name] = $db->describeReferences($name, $schema);
        }

        return $referenceList;
    }

    /**
     * Set path to folder where models are
     *
     * @throw InvalidParameterException
     */
    protected function setModelsDir(): void
    {
        if ($this->modelOptions->hasOption('modelsDir')) {
            $this->modelOptions->setOption(
                'modelsDir',
                rtrim($this->modelOptions->getOption('modelsDir'), '/\\') . DIRECTORY_SEPARATOR
            );
            return;
        }

        if ($modelsDir = $this->modelOptions->getOption('config')->path('application.modelsDir')) {
            $this->modelOptions->setOption('modelsDir', rtrim($modelsDir, '/\\') . DIRECTORY_SEPARATOR);
            return;
        }

        throw new InvalidParameterException("Builder doesn't know where is the models directory.");
    }

    /**
     * Returns the associated PHP type
     *
     * @param  int $type
     * @return string
     */
    protected function getPHPType(int $type): string
    {
        switch ($type) {
            case Column::TYPE_INTEGER:
            case Column::TYPE_TINYINTEGER:
            case Column::TYPE_SMALLINTEGER:
            case Column::TYPE_MEDIUMINTEGER:
            case Column::TYPE_BIGINTEGER:
            case Column::TYPE_BIT:
                return 'int';

            case Column::TYPE_DECIMAL:
            case Column::TYPE_FLOAT:
            case Column::TYPE_DOUBLE:
                return 'float';

            case Column::TYPE_BOOLEAN:
                return 'bool';

            case Column::TYPE_DATE:
            case Column::TYPE_DATETIME:
            case Column::TYPE_TIME:
            case Column::TYPE_CHAR:
            case Column::TYPE_VARCHAR:
            case Column::TYPE_TEXT:
            case Column::TYPE_TINYTEXT:
            case Column::TYPE_MEDIUMTEXT:
            case Column::TYPE_LONGTEXT:
            case Column::TYPE_JSON:
            case Column::TYPE_JSONB:
            default:
                return 'string';
        }
    }

    /**
     * Extract a semantic role from a foreign-key field.
     *
     * Examples:
     *
     * team_id              + teams => ''
     * home_team_id         + teams => Home
     * team_home_id         + teams => Home
     * team_id_home         + teams => Home
     *
     * user_id              + users => ''
     * user_id_updated      + users => Updated
     * updated_user_id      + users => Updated
     *
     * created_by_user_id   + users => CreatedBy
     *
     * @param string $field
     * @param string $referencedTable
     *
     * @return string
     */
    protected function getRelationRole(
        string $field,
        string $referencedTable
    ): string {
        /**
         * Normalize camelCase/PascalCase before splitting.
         *
         * userIdUpdated => user_Id_Updated
         */
        $normalizedField = preg_replace(
            '/([a-z0-9])([A-Z])/',
            '$1_$2',
            $field
        );

        $fieldParts = preg_split(
            '/[_-]+/',
            strtolower((string) $normalizedField),
            -1,
            PREG_SPLIT_NO_EMPTY
        );

        if (!$fieldParts) {
            return '';
        }

        /**
         * "id" describes the FK implementation, not its semantic role.
         *
         * user_id_updated
         *     => user, updated
         *
         * updated_user_id
         *     => updated, user
         */
        $fieldParts = array_values(
            array_filter(
                $fieldParts,
                static fn(string $part): bool => $part !== 'id'
            )
        );

        if (!$fieldParts) {
            return '';
        }

        $normalizedTable = preg_replace(
            '/([a-z0-9])([A-Z])/',
            '$1_$2',
            $referencedTable
        );

        $tableParts = preg_split(
            '/[_-]+/',
            strtolower((string) $normalizedTable),
            -1,
            PREG_SPLIT_NO_EMPTY
        );

        if (!$tableParts) {
            return Utils::camelize(
                implode('_', $fieldParts),
                '_-'
            );
        }

        /**
         * Convert the final table token to its probable singular form.
         *
         * teams      => team
         * users      => user
         * categories => category
         * statuses   => status
         */
        $lastIndex = count($tableParts) - 1;

        $tableParts[$lastIndex] = $this->singularizeRelationToken(
            $tableParts[$lastIndex]
        );

        /**
         * Remove the referenced entity wherever it appears in the FK.
         *
         * home_team       - team => home
         * team_home       - team => home
         * user_updated    - user => updated
         * updated_user    - user => updated
         */
        $fieldParts = $this->removeRelationEntityParts(
            $fieldParts,
            $tableParts
        );

        if (!$fieldParts) {
            /**
             * No remaining parts means this is the canonical FK.
             *
             * team_id => ''
             * user_id => ''
             */
            return '';
        }

        return Utils::camelize(
            implode('_', $fieldParts),
            '_-'
        );
    }

    /**
     * Singularize the last token of a table name only for relation-role
     * detection. This is deliberately conservative and is not intended
     * to be a general-purpose inflector.
     */
    protected function singularizeRelationToken(string $token): string
    {
        $length = strlen($token);

        if ($length <= 1) {
            return $token;
        }

        if ($length > 3 && str_ends_with($token, 'ies')) {
            return substr($token, 0, -3) . 'y';
        }

        if ($length > 3 &&
            (
                str_ends_with($token, 'sses') ||
                str_ends_with($token, 'xes') ||
                str_ends_with($token, 'zes') ||
                str_ends_with($token, 'ches') ||
                str_ends_with($token, 'shes') ||
                str_ends_with($token, 'ses')
            )
        ) {
            return substr($token, 0, -2);
        }

        if ($length > 2 &&
            str_ends_with($token, 's') &&
            !str_ends_with($token, 'ss')
        ) {
            return substr($token, 0, -1);
        }

        return $token;
    }

    /**
     * Remove the referenced entity tokens from the FK tokens.
     *
     * @param array $fieldParts
     * @param array $entityParts
     *
     * @return array
     */
    protected function removeRelationEntityParts(
        array $fieldParts,
        array $entityParts
    ): array {
        $fieldCount  = count($fieldParts);
        $entityCount = count($entityParts);

        if ($entityCount === 0 || $entityCount > $fieldCount) {
            return $fieldParts;
        }

        for ($i = 0; $i <= $fieldCount - $entityCount; $i++) {
            $matches = true;

            for ($j = 0; $j < $entityCount; $j++) {
                if ($fieldParts[$i + $j] !== $entityParts[$j]) {
                    $matches = false;
                    break;
                }
            }

            if (!$matches) {
                continue;
            }

            array_splice($fieldParts, $i, $entityCount);

            return array_values($fieldParts);
        }

        return $fieldParts;
    }
}
