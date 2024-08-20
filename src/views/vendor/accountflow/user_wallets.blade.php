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
                        Users Wallets / Account Balance
                    </h1>
                    <!--end::Title-->
                </div>
                <!--end::Page title-->
            </div>
            <!--end::Toolbar wrapper-->
        </div>
        <!--end::Toolbar container-->
    </div>
    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container container-fluid">
        <!--begin::Content-->


        @AF_dtable_btns(['search' => true, 'colvis' => true, 'export_btn' => true])
        <div class="border rounded p-md-10 mt-2">
            <table class="table align-middle table-row-dashed fs-6 g-5 px-5" id="userwallets_dtable">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Wallet Balance</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>

        </div>
        @AF_dtable([
            //Table ID
            'id' => 'userwallets_dtable',
            'order' => ['0', 'asc'],
            'hide_cols' => '[]',
            'pageLength' => '50',
            'route' => 'get.userwallets',
            'cols' => ['user_id', 'balance', 'status', 'actions'],
            'cols_class' => [['0', 'text-gray-700'], ['1', 'text-gray-900 fw-bold']],
        ])

        <!--end::Content-->
    </div>
    <!--end::Content container-->
@endsection
