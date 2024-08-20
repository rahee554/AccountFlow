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
                        Assets
                    </h1>
                    <!--end::Title-->
                </div>
                <!--end::Page title-->
                <!--begin::Actions-->
                <div class="d-flex align-items-center gap-2 gap-lg-3">
                    <a href="{{ route('accounts.assets.trx') }}" class="btn btn-light">Transactions List</a>
                    <a href="#"
                        class="btn btn-flex btn-outline btn-color-gray-700 btn-active-color-primary bg-body h-40px fs-7 fw-bold"
                        data-bs-toggle="modal" data-bs-target="#create_asset">
                        Create Asset
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


            <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-6">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#assets_list">Assets List</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#assets_trx">Asset Transactions</a>
                </li>
            </ul>

            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="assets_list" role="tabpanel">
                    <div class="rounded border p-sm-5">
                        @AF_dtable_btns(['export_btn' => 'true', 'colvis' => 'true', 'search' => 'true', 'index'=>'1'])
                        <table class="table table-responsive" id="assets_dtable">
                            <thead class="text-uppercase text-gray-700 fw-bold">
                                <th>
                                    <tr>
                                        <td>Asset Name</td>
                                        <td>Description</td>
                                        <td>Value</td>
                                        <td>Date</td>
                                        <td>Category</td>
                                        <td>status</td>
                                        <td>Transactions</td>
                                        <td>Action</td>
                                    </tr>
                                </th>

                            </thead>
                            <tbody></tbody>
                        </table>
                        @AF_dtable([
                            //Table ID
                            'index' => '1',
                            'id' => 'assets_dtable',
                            'route' => 'get.assets.list',
                            'cols' => ['name', 'description', 'value', 'date', 'category', 'status', 'transactions', 'actions'],
                            'cols_class' => [['1', 'text-gray-700'], ['0', 'text-gray-900 fw-bold']],
                        ])
                    </div>
                </div>
                <div class="tab-pane fade" id="assets_trx" role="tabpanel">
                    <div class="rounded border p-sm-5">
                        @AF_dtable_btns(['export_btn' => 'true', 'colvis' => 'true', 'search' => 'true', 'index'=>'2'])
                        <table class="table table-responsive" id="assets_trx_dtable">
                            <thead class="text-uppercase text-gray-700 fw-bold">
                                <th>
                                    <tr>
                                        <td>TrxID</td>
                                        <td>Asset Name</td>
                                        <td>Value</td>
                                        <td>Amount</td>
                                        <td>Category</td>
                                        <td>Trx Date</td>
                                        <td>Action</td>
                                    </tr>
                                </th>
        
                            </thead>
                            <tbody></tbody>
                        </table>
        
                        @AF_dtable([
                            //Table ID
                            'index' => '2',
                            'id' => 'assets_trx_dtable',
                            'route' => 'get.assets.trx',
                            'cols' => ['unique_id', 'name', 'value', 'amount', 'category', 'date', 'actions'],
                            'cols_class' => [['0', 'text-gray-700'], ['1', 'text-gray-900 fw-bold']],
                        ])
                    </div>
        
                </div>

            </div>



        </div>


        <!--end::Content-->
    </div>
    <!--end::Content container-->

    @include('lab.modals.accounts.create_assets')
@endsection
