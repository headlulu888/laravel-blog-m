@if ($errors->any())
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="alert alert-danger">
                    <ui>
                        @foreach ($errors->all() as $error)
                            <li>{{$error}}</li>
                        @endforeach
                    </ui>
                </div>
            </div>
        </div>
    </div>
@endif