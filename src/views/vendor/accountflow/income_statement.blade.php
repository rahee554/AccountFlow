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
                        Income Statement
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

        <h4>Income</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($incomeCategories as $category => $amount)
                    <tr>
                        <td>{{ $category }}</td>
                        <td>{{ $amount }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Expenses Section -->
        <h4>Expenses</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expenseCategories as $category => $amount)
                    <tr>
                        <td>{{ $category }}</td>
                        <td>{{ $amount }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>


        <!--end::Content-->
    </div>
    <!--end::Content container-->
@endsection
