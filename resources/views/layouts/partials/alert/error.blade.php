@if (Session::has('error'))
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
        <x-snd-icon name="circle-alert" class="mr-2" />
        <span>{{ Session::get('error') }}</span>
        <button type="button" class="close ml-auto" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    </div>
@endif
