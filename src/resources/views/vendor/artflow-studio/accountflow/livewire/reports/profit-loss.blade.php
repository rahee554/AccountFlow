   <div>
          @include(config('accountflow.view_path') . '.blades.dashboard-header')

        {{-- Print Controls --}}
        <div class="print-controls d-print-none mb-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h4 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>
                        Profit & Loss Report
                    </h4>
                    <small class="text-muted">Generate and print financial statements</small>
                </div>
                <div class="col-md-6 text-end">
                    <div class="d-flex justify-content-end align-items-center gap-3">
                        <div class="date-picker-wrapper">
                            <label for="daterange" class="form-label small mb-1">Report Period:</label>
                            <input type="text" class="form-control form-control-sm" id="daterange" 
                                   wire:model="dateRange" style="width: 220px;" placeholder="Select date range">
                        </div>
                        <div>
                            <label class="form-label small mb-1">&nbsp;</label>
                            <div>
                                <button class="btn btn-outline-secondary btn-sm me-2" wire:click="loadReportData">
                                    <i class="fas fa-sync-alt me-1"></i>Refresh
                                </button>
                                <button class="btn btn-primary btn-sm" wire:click="printReport">
                                    <i class="fas fa-print me-1"></i>Print Report
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Loading Indicator --}}
        <div wire:loading class="text-center py-5 d-print-none">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="mt-3">
                <h5 class="text-muted">Generating Financial Report...</h5>
                <small class="text-muted">Please wait while we compile your data</small>
            </div>
        </div>

        {{-- A4 Report Container --}}
        <div class="a4-report" wire:loading.remove>
            {{-- Report Header --}}
            <div class="report-header">
                <div class="company-name">{{ $companyInfo['name'] }}</div>
                <div class="company-details">
                    <div><i class="fas fa-map-marker-alt me-1"></i>{{ $companyInfo['address'] }}</div>
                    <div>{{ $companyInfo['city'] }}</div>
                    <div>
                        <i class="fas fa-phone me-1"></i>{{ $companyInfo['phone'] }} | 
                        <i class="fas fa-envelope me-1"></i>{{ $companyInfo['email'] }}
                    </div>
                </div>
                
                <div class="report-title">
                    <h3 class="report-name">Profit and Loss Statement</h3>
                    <div class="report-period">
                        <i class="fas fa-calendar-alt me-1"></i>
                        For the Period: {{ \Carbon\Carbon::parse($startDate)->format('F d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('F d, Y') }}
                    </div>
                </div>
            </div>

            {{-- Report Summary Box --}}
            <div class="summary-box d-print-none">
                <h5 class="mb-3"><i class="fas fa-chart-pie me-2"></i>Financial Summary</h5>
                <div class="row">
                    <div class="col-4">
                        <div class="summary-item">
                            <span>Total Revenue:</span>
                            <span class="fw-bold text-success">{{ config('accountflow.currency', '$') }}{{ number_format($reportData['total_revenue'], 2) }}</span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="summary-item">
                            <span>Total Expenses:</span>
                            <span class="fw-bold text-danger">{{ config('accountflow.currency', '$') }}{{ number_format($reportData['total_expenses'], 2) }}</span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="summary-item">
                            <span>Net Income:</span>
                            <span class="fw-bold {{ $reportData['net_income'] >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ config('accountflow.currency', '$') }}{{ number_format(abs($reportData['net_income']), 2) }}
                                {{ $reportData['net_income'] < 0 ? '(Loss)' : '' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Report Body --}}
            <div class="report-body">
                {{-- REVENUE SECTION --}}
                <div class="section-revenue">
                    <h4 class="section-title">
                        <i class="fas fa-arrow-up text-success me-2"></i>Revenue
                    </h4>
                    
                    @if($reportData['revenue']->count() > 0)
                        @foreach($reportData['revenue'] as $revenueCategory)
                            <div class="line-item">
                                <div class="account-name">
                                    <i class="fas fa-dot-circle me-2" style="font-size: 8pt;"></i>
                                    {{ $revenueCategory->name }}
                                </div>
                                <div class="amount">{{ config('accountflow.currency', '$') }}{{ number_format($revenueCategory->total_amount, 2) }}</div>
                            </div>
                            
                            {{-- Show subcategories if any --}}
                            @php
                                $subCategories = $reportData['categories']->where('parent_id', $revenueCategory->id)->where('total_amount', '>', 0);
                            @endphp
                            @if($subCategories->count() > 0)
                                <div class="category-breakdown">
                                    @foreach($subCategories as $subCategory)
                                        <div class="category-item">
                                            <div>
                                                <i class="fas fa-chevron-right me-2" style="font-size: 7pt;"></i>
                                                {{ $subCategory->name }}
                                            </div>
                                            <div>{{ config('accountflow.currency', '$') }}{{ number_format($subCategory->total_amount, 2) }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @endforeach
                        
                        <div class="total-line">
                            <div class="account-name">
                                <i class="fas fa-calculator me-2"></i>
                                <strong>TOTAL REVENUE</strong>
                            </div>
                            <div class="amount">
                                <strong>{{ config('accountflow.currency', '$') }}{{ number_format($reportData['total_revenue'], 2) }}</strong>
                            </div>
                        </div>
                    @else
                        <div class="no-data-message">
                            <i class="fas fa-info-circle me-2"></i>
                            No revenue recorded for this period
                        </div>
                        <div class="total-line">
                            <div class="account-name"><strong>TOTAL REVENUE</strong></div>
                            <div class="amount"><strong>{{ config('accountflow.currency', '$') }}0.00</strong></div>
                        </div>
                    @endif
                </div>

                {{-- EXPENSES SECTION --}}
                <div class="section-expenses">
                    <h4 class="section-title">
                        <i class="fas fa-arrow-down text-danger me-2"></i>Expenses
                    </h4>
                    
                    @if($reportData['expenses']->count() > 0)
                        @foreach($reportData['expenses'] as $expenseCategory)
                            <div class="line-item">
                                <div class="account-name">
                                    <i class="fas fa-dot-circle me-2" style="font-size: 8pt;"></i>
                                    {{ $expenseCategory->name }}
                                </div>
                                <div class="amount">{{ config('accountflow.currency', '$') }}{{ number_format(abs($expenseCategory->total_amount), 2) }}</div>
                            </div>
                            
                            {{-- Show subcategories if any --}}
                            @php
                                $subCategories = $reportData['categories']->where('parent_id', $expenseCategory->id)->where('total_amount', '<', 0);
                            @endphp
                            @if($subCategories->count() > 0)
                                <div class="category-breakdown">
                                    @foreach($subCategories as $subCategory)
                                        <div class="category-item">
                                            <div>
                                                <i class="fas fa-chevron-right me-2" style="font-size: 7pt;"></i>
                                                {{ $subCategory->name }}
                                            </div>
                                            <div>{{ config('accountflow.currency', '$') }}{{ number_format(abs($subCategory->total_amount), 2) }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @endforeach
                        
                        <div class="total-line">
                            <div class="account-name">
                                <i class="fas fa-calculator me-2"></i>
                                <strong>TOTAL EXPENSES</strong>
                            </div>
                            <div class="amount">
                                <strong>{{ config('accountflow.currency', '$') }}{{ number_format($reportData['total_expenses'], 2) }}</strong>
                            </div>
                        </div>
                    @else
                        <div class="no-data-message">
                            <i class="fas fa-info-circle me-2"></i>
                            No expenses recorded for this period
                        </div>
                        <div class="total-line">
                            <div class="account-name"><strong>TOTAL EXPENSES</strong></div>
                            <div class="amount"><strong>{{ config('accountflow.currency', '$') }}0.00</strong></div>
                        </div>
                    @endif
                </div>

                {{-- NET INCOME SECTION --}}
                <div class="section-net-income">
                    <div class="net-income-line">
                        <div class="account-name">
                            <i class="fas fa-trophy me-2"></i>
                            <strong>NET {{ $reportData['net_income'] >= 0 ? 'INCOME' : 'LOSS' }}</strong>
                        </div>
                        <div class="amount net-amount {{ $reportData['net_income'] >= 0 ? 'profit' : 'loss' }}">
                            <strong>{{ config('accountflow.currency', '$') }}{{ number_format(abs($reportData['net_income']), 2) }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Report Footer --}}
            <div class="report-footer">
                <div class="footer-info">
                    <div class="row">
                        <div class="col-6">
                            <div><i class="fas fa-user me-1"></i><strong>Generated by:</strong> {{ auth()->user()->name ?? 'System Administrator' }}</div>
                            <div><i class="fas fa-clock me-1"></i><strong>Generated on:</strong> {{ now()->format('F d, Y \a\t H:i A') }}</div>
                            <div><i class="fas fa-calendar me-1"></i><strong>Report Date:</strong> {{ now()->format('Y-m-d') }}</div>
                        </div>
                        <div class="col-6 text-end">
                            <div><i class="fas fa-id-badge me-1"></i><strong>User ID:</strong> rahee554</div>
                            <div><i class="fas fa-file me-1"></i><strong>Document:</strong> P&L Statement</div>
                            <div><i class="fas fa-hashtag me-1"></i><strong>Page:</strong> 1 of 1</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
