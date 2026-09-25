# Contributing to Phalcon Developer Tools

Phalcon Developer Tools is an open-source project and a volunteer effort.

Contributions from everyone are welcome.

## Contributions

Contributions to Phalcon Developer Tools should be submitted as GitHub pull requests.

Each pull request will be reviewed by a project contributor and will either be merged or receive feedback describing any changes required before it can be accepted.

Before starting a significant feature or architectural change, consider discussing the proposal with the Phalcon team first.

## Questions & Support

GitHub issues are intended for bug reports and feature requests.

For questions about using Phalcon or Phalcon Developer Tools, please use the official Phalcon Discussions or Discord support channels.

## Bug Report Checklist

Before submitting a bug report:

- Make sure you are using the latest applicable version of Phalcon Framework and Phalcon Developer Tools.
- Include enough information to reproduce the issue.
- Include the operating system, PHP version, Phalcon version and Phalcon DevTools version.
- Include database type and version when the issue involves database functionality.
- Provide a minimal reproducer, failing test or small repository whenever possible.

## Pull Request Checklist

Create your branch from the appropriate source branch for the change. Do not assume that `master` is the correct target branch.

Before submitting a pull request:

- Rebase your branch when necessary.
- Keep the change focused on the purpose of the pull request.
- Add or update automated tests for behavior changes and bug fixes.
- Update documentation when behavior or requirements change.
- Update `CHANGELOG.md` when appropriate.
- Avoid unrelated dependency or lock-file changes.
- Make sure the code follows the project's coding standards.
- Run the relevant test suites and static-analysis tools.

The Phalcon contribution guidelines also require disclosure when AI coding assistants materially contribute to a change. Review the current Phalcon AI Development guidelines before submitting your pull request.

## Development Checks

Run the available Codeception test suites:

```bash
vendor/bin/codecept run
```

Run PHPStan:

```bash
vendor/bin/phpstan analyse
```

Run Psalm:

```bash
vendor/bin/psalm --threads=1
```

Run PHP_CodeSniffer:

```bash
vendor/bin/phpcs
```

## Getting Support

For questions about using Phalcon, use the official Phalcon Discussions or Discord support channels.

## Requesting Features

For significant new functionality, review the current Phalcon New Feature Request guidelines before implementation.

Thanks!

Phalcon Team
