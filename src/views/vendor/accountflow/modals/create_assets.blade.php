@php
    $accounts = App\Models\AccountFlow\Account::where('status', 1)->get();
    $expense_categories = App\Models\AccountFlow\Category::where('flow_type', 2)
        ->where('status', 1)
        ->WhereNotNull('parent_id')
        ->get();
@endphp

<div class="modal fade" tabindex="-1" id="create_asset">
    <div class="modal-dialog modal-lg">
        <form id="createAsset" class="repeater">
            @csrf
            <div class="modal-content">
                <div class="modal-header py-5">
                    <h5 class="modal-title"> Add Transfers</h5>
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal"
                        aria-label="Close">
                        <span class="svg-icon"><i class="fas fa-times"></i></span>
                    </div>
                </div>

                <div class="modal-body">

                    <div class="row">

                        <div class="py-5 col-12 col-md-4">
                            <label for="date" class="text-uppercase  text-gray-600">Asset Name</label>
                            <input type="text" name="name" class="form-control form-control-sm text-dark"
                                placeholder="Asset Name">
                        </div>


                        <div class="py-5 col-12 col-md-4">
                            <label for="date" class="text-uppercase  text-gray-600">Asset Value</label>
                            <input type="text" name="value" class="form-control form-control-sm text-dark" placeholder="Value">
                        </div>

                        <div class="py-5 col-12 col-md-4">
                            <label for="date" class="text-uppercase  text-gray-600">Category</label>
                            <select class="form-select form-select-sm" name="category" data-control="select2"
                                data-dropdown-parent="#create_asset" data-placeholder="Select an option">

                                <option value=""></option>
                                @foreach ($expense_categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}
                                    </option>
                                @endforeach

                            </select>
                        </div>



                        <div class="py-5 col-12 col-md-4">
                            <label for="date" class="text-uppercase  text-gray-600">Aquisiition
                                Date</label>
                            <input type="date" name="date" class="form-control form-control-sm text-dark " placeholder="Date">
                        </div>


                        <div class="py-5 col-12 col-md-4">
                            <label for="status" class="text-uppercase text-gray-600">Status</label>
                            <select name="status" id="" class="form-select form-select-sm">
                                <option value="1">Active / Operating</option>
                                <option value="2">Inactive</option>
                            </select>
                        </div>

                        <div class="py-5 col-12">
                            <textarea name="description" id="" cols="30" rows="3" class="form-control"
                                placeholder="Details / Description"></textarea>
                        </div>
                    </div>

                    <div class="separator"></div>
                    <!--begin::Repeater-->
                    <div id="assetArray">
                        <!--begin::Form group-->
                        <div class="form-group">
                            <div data-repeater-list="assetArray">
                                <div data-repeater-item>
                                    <div class="form-group row">
                                        <div class="col-md-3">
                                            <label class="form-label">Amount</label>
                                            <input type="number" name="amount"
                                                class="form-control form-control-sm mb-2 mb-md-0"
                                                placeholder="Trx Amount" />
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label">Account</label>
                                            <select name="account" id="" class="form-select form-select-sm">
                                                <option value=""></option>
                                                @php
                                                    $accounts = App\Models\AccountFlow\Account::all();
                                                @endphp
                                                @foreach ($accounts as $account)
                                                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                                                @endforeach

                                            </select>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label">Date</label>
                                            <input type="date" name="date"
                                                class="form-control form-control-sm mb-2 mb-md-0"
                                                placeholder="Transaction Date" />
                                        </div>

                                        <div class="col-md-3">
                                            <a href="javascript:;" data-repeater-delete
                                                class="btn btn-sm btn-light-danger mt-3 mt-md-8">
                                                <i class="ki-duotone ki-trash fs-5"><span class="path1"></span><span
                                                        class="path2"></span><span class="path3"></span><span
                                                        class="path4"></span><span class="path5"></span></i>
                                                Delete
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Form group-->

                        <!--begin::Form group-->
                        <div class="form-group mt-5">
                            <a href="javascript:;" data-repeater-create class="btn btn-sm btn-light-primary">
                                <i class="ki-duotone ki-plus fs-3"></i>
                                Add Transaction
                            </a>
                        </div>
                        <!--end::Form group-->
                    </div>
                    <!--end::Repeater-->


                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
        </form>
    </div>
</div>

@push('scripts')
    <script>
        $('#assetArray').repeater({
            initEmpty: false,

            defaultValues: {
                'text-input': 'foo'
            },

            show: function() {
                $(this).slideDown();
                // Re-init flatpickr
                $(this).find('[data-kt-repeater="datepicker"]').flatpickr();
            },

            hide: function(deleteElement) {
                $(this).slideUp(deleteElement);
            },

            ready: function() {

                // Init flatpickr
                $('[data-kt-repeater="datepicker"]').flatpickr();

            }
        });
    </script>
@endpush

@AF_AjaxForm([
    'id' => 'createAsset',
    'route' => 'accounts.assets.create',
    'method' => 'post',
    'logType' => 'swal',
    'onSuccess' => [
        'log' => 'swal', // options: 'swal', 'alert', 'console'
        'reload' => true, // true or false
        //'dtable' => 'transfers_dtable', // ID of the DataTable to reinitialize
        //'reset' => true,
        //'disableSubmit' => true,
    ],
])
