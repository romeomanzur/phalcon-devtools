<div class="row">
    <nav>
        <ul class="pager">
            <li class="previous">{{ a(url("$plural$"), "Go Back") }}</li>
        </ul>
    </nav>
</div>

<div class="page-header">
    <h1>Edit $plural$</h1>
</div>

{{ content() }}

<form action="{{ url('$plural$/save') }}" class="form-horizontal" method="post">
    $captureFields$

    <input type="hidden" name="$pk$" value="{{ $singular$.$pk$ }}">

    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            <input type="submit" name="save" value="Save" class="btn btn-primary">
        </div>
    </div>
</form>
