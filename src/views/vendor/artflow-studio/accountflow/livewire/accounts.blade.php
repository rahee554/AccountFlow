<div>
    
        <div id="kt_app_toolbar" class="app-toolbar  pt-6 pb-2 ">
            <!--begin::Toolbar container-->
            <div id="kt_app_toolbar_container" class="app-container  container-fluid d-flex align-items-stretch ">
                <!--begin::Toolbar wrapper-->
                <div class="app-toolbar-wrapper d-flex flex-stack flex-wrap gap-4 w-100">
                    <!--begin::Page title-->
                    <div class="page-title d-flex flex-column justify-content-center gap-1 me-3">
                        <!--begin::Title-->
                        <h1 class="page-heading d-flex flex-column justify-content-center text-dark fw-bold fs-3 m-0">
                            Accounts List
                        </h1>
                        <!--end::Title-->
                    </div>
                    <!--end::Page title-->
                    <!--begin::Actions-->
                    <div class="d-flex align-items-center gap-2 gap-lg-3">
                        <a href="#"
                            class="btn btn-sm btn-flex btn-outline btn-color-gray-700 btn-active-color-primary bg-body fs-7 fw-bold"
                            data-bs-toggle="modal" data-bs-target="#accounts_transfer">
                            Create Trasfer
                        </a>
    
    
    
    
                        <a href="#"
                            class="btn btn-sm btn-flex btn-outline btn-color-gray-700 btn-active-color-primary bg-body fs-7 fw-bold"
                            data-bs-toggle="modal" data-bs-target="#createAccount">
                            Create Account
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
                        <a class="nav-link active" data-bs-toggle="tab" href="#accounts_list">Accounts List</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#transfers">Transfer Between Accounts</a>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="accounts_list" role="tabpanel">
    
    
    
                            @livewire('aftable', [
                                'model' => 'App\Models\AccountFlow\Account',
                                'columns' => [
                                    ['key' => 'name', 'label' => 'Account Title'],
                                    ['key' => 'description', 'label' => 'Description'],
                                    ['key' => 'balance', 'label' => 'Account Balance'],
                                    ['key' => 'status', 'label' => 'Status', 'raw' => '{!! $row->status == 1 ? "<span class=\"badge bafge-sm badge-light-success\">Active</span>" : "<span class=\"badge badge-sm badge-light-warning\">Inactive</span>" !!}'],
                                ]
                            ])
    
    
                    </div>
                </div>
    
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade" id="transfers" role="tabpanel">
                            @livewire('aftable', [
                                'model' => 'App\Models\AccountFlow\Transfer',
                                'columns' => [
                                    ['key' => 'unique_id', 'label' => 'ID'],
                                    [
                                        'key' => 'amount',
                                        'label' => 'Amount',
                                        'raw' => '<span class="{{ $row->type == 1 ? "text-success" : "text-danger" }}">Rs: {{ $row->amount }}</span>'
                                    ],
                                    ['key' => 'from_account', 'label' => 'From Account', 'relation' => 'fromAccount:name'],
                                    ['key' => 'to_account', 'label' => 'To Account', 'relation' => 'toAccount:name'],
                                    ['key' => 'description', 'label' => 'Description'],
                                    ['key' => 'date', 'label' => 'Date', 'raw' => '{{ \Carbon\Carbon::parse($row->date)->format("d M Y") }}'],
                                    ['key' => 'created_by', 'label' => 'Created By' , 'relation' => 'user:name'],
                                ],
                            ])
                        </div>
                </div>
            </div>
    
            {{-- @include(config('accountflow.view_path') . 'modals.transfers') --}}
            <!--end::Content-->
        </div>
        <!--end::Content container-->
    
    
</div>
