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
                        Accounts Categories
                    </h1>
                    <!--end::Title-->
                </div>
                <!--end::Page title-->
                <!--begin::Actions-->
                <div class="d-flex align-items-center gap-2 gap-lg-3">
                    <a href="#"
                        class="btn btn-flex btn-outline btn-color-gray-700 btn-active-color-primary bg-body h-40px fs-7 fw-bold"
                        data-bs-toggle="modal" data-bs-target="#kt_modal_view_users">
                        Create Category
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






        @AF_dtable_btns(['search' => true, 'colvis' => true, 'export_btn' => true])
        <div class="border rounded p-md-10 mt-2">
            <table class="table align-middle table-row-dashed fs-6 g-5 px-5" id="ac_categories_dtable">
                <thead>
                    <tr>
                        <th>Category Name</th>
                        <th>Type</th>
                        <th>Parent Category</th>
                        <th>Stauts</th>
                        <th>Privacy</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>

        </div>
        @AF_dtable([
            //Table ID
            'id' => 'ac_categories_dtable',
            'order' => ['0', 'asc'],
            'pageLength' => '50',
            'route' => 'get.accounts.categories',
            'cols' => ['name', 'flow_type', 'parent_id', 'status', 'privacy', 'actions'],
            'cols_class' => [['0', 'text-gray-700'], ['1', 'text-gray-900 fw-bold']],
        ])




        <!--end::Content-->
    </div>
    <!--end::Content container-->
@endsection
