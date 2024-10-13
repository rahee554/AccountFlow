@extends(config('accountflow.layout'))
@section('content')
    <div id="kt_app_toolbar" class="app-toolbar  pt-6 pb-2 ">
        <!--begin::Toolbar container-->
        <div id="kt_app_toolbar_container" class="app-container  container-fluid d-flex align-items-stretch ">
            <!--begin::Toolbar wrapper-->
            <div class="app-toolbar-wrapper d-flex flex-stack flex-wrap gap-4 w-100">
                <!--begin::Page title-->
                <div class="page-title d-flex flex-column justify-content-center gap-1 me-3">
                    <!--begin::Title-->
                    <h1 class="page-heading d-flex flex-column justify-content-center text-dark fw-bold fs-3 m-0">
                        Transfers List
                    </h1>
                    <!--end::Title-->
                </div>
                <!--end::Page title-->
                <!--begin::Actions-->
                <div class="d-flex align-items-center gap-2 gap-lg-3">
                    <a href="#"
                        class="btn btn-flex btn-outline btn-color-gray-700 btn-active-color-primary bg-body h-40px fs-7 fw-bold"
                        data-bs-toggle="modal" data-bs-target="#accounts_transfer">
                        Create Transfer
                    </a>
                </div>
                <!--end::Actions-->
            </div>
            <!--end::Toolbar wrapper-->
        </div>
        <!--end::Toolbar container-->
    </div>
    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container container-fluid">
        <!--begin::Content-->

        <div class="container">


            <div class="rounded border p-sm-5">
                @AF_dtable_btns(['export_btn' => 'true', 'colvis' => 'true', 'search' => 'true'])
                <table class="table table-responsive" id="transfers_dtable">
                    <thead class="text-uppercase text-gray-700 fw-bold">
 <tr>
                                <th>ID</th>
                                <th>amount</th>
                                <th>From Account</th>
                                <th>To Account</th>
                                <th>Description</th>
                                <th>Date</th>
                                <th>Created By</th>
                                <th>Action</th>
                            <tr>
                    </thead>
                    <tbody></tbody>
                </table>

                @AF_dtable([
                    //Table ID
                    'id' => 'transfers_dtable',
                    'hide_cols' => [],
                    'route' => 'get.transfers.list',
                    'cols' => ['unique_id', 'amount', 'from_account', 'to_account', 'description', 'date', 'created_by', 'actions'],
                    #'cols_class' => [['index' => '0', 'class' => 'text-gray-700'], ['index' => '1', 'class' => 'text-gray-900 fw-bold']],
                    #'render' => [['col' => 6, 'html' => '<a href="mailto:${data}">${data}</a>']],
                ])
            </div>

        </div>



        <!--end::Content-->
    </div>
    <!--end::Content container-->

    @include('lab.modals.accounts.transfers')
@endsection
