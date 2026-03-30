@extends('layouts.master')

@section('title') Data Management @endsection

@section('style')
    <style>
        body[data-sidebar=dark].vertical-collpsed {
            min-height: auto !important;
        }
        /* .simplebar-content-wrapper {
            height: 100% !important;
            overflow: hidden scroll !important;
        }
        .vertical-collpsed .vertical-menu .simplebar-content-wrapper, .vertical-collpsed .vertical-menu .simplebar-mask, .vertical-collpsed .vertical-menu .simplebar-content-wrapper, .vertical-collpsed .vertical-menu .simplebar-mask {
            overflow: visible;
        } */
        .custom-heading {
            color: #3f4784;
        }

        .hover-link {
            padding: 4px 0px;
            border-radius: 8px;
            display: inline-block;
            transition: all 0.3s ease-in-out;
        }

        .hover-link:hover {
            /* background-color: #f5f5f5; */
            font-weight: bold;
            /* transform: translateX(5px); */
            transform: scale(1.05);
            /* box-shadow: 0 2px 6px rgba(0,0,0,0.08); */
            text-decoration: none;
        }

        .hover-box {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            z-index: 0;
        }

        .hover-box::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 100%;
            border-radius: 12px;
            border: 2px solid transparent;
            box-sizing: border-box;
            z-index: 1;
            pointer-events: none;
            border-image: linear-gradient(90deg, transparent, #0d6efd, transparent) 1;
            border-image-slice: 1;
            animation: border-move 2s linear infinite;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .hover-box:hover::before {
            opacity: 1;
        }

        @keyframes border-move {
            0% {
                border-image-source: linear-gradient(90deg, transparent, #3f4784, transparent);
            }
            25% {
                border-image-source: linear-gradient(180deg, transparent, #3f4784, transparent);
            }
            50% {
                border-image-source: linear-gradient(270deg, transparent, #3f4784, transparent);
            }
            75% {
                border-image-source: linear-gradient(360deg, transparent, #3f4784, transparent);
            }
            100% {
                border-image-source: linear-gradient(90deg, transparent, #3f4784, transparent);
            }
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card border">
                <div class="card-body">
                    <div class="row row-cols-2 row-cols-lg-3 row-cols-xl-4 g-3">

                        @if (Can::is_accessible('clients') || Can::is_accessible('staff') || Can::is_accessible('promoters') || Can::is_accessible('roles') || Can::is_accessible('sub_admins') || Can::is_accessible('login_requests') || Can::is_accessible('leaves') || Can::is_accessible('hierarchies'))
                            <div class="col ">
                                <div class="p-3 h-100 d-flex hover-box">
                                    <div class="me-3 d-flex align-items-start">
                                        <i class="bx bxs-user-detail fs-1 "></i>
                                    </div>
                                    <div>
                                        <h6 class="custom-heading fw-bold">Users & Permissions </h6>
                                        <ul class="list-unstyled mt-2">
                                            @if (Can::is_accessible('clients'))
                                                <li><a href="{{ route('admin.clients.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> Clients </a></li>
                                            @endif
                                            @if (Can::is_accessible('promoters'))
                                                <li><a href="{{ route('admin.promoters.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> Promoters </a></li>
                                            @endif
                                            @if (Can::is_accessible('staff'))
                                                <li><a href="{{ route('admin.staff.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> Manage Staff </a></li>
                                                <li><a href="{{ route('admin.hierarchies.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> Manage hierarchy </a></li>
                                            @endif
                                            {{-- @if (Can::is_accessible('staff'))
                                                <li><a href="{{ route('admin.staff.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> All Staff </a></li>
                                            @endif --}}
                                            {{-- @if (Can::is_accessible('sub_admins'))
                                                <li><a href="{{ route('admin.sub_admins.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> All Sub Admins </a></li>
                                            @endif --}}
                                            @if (Can::is_accessible('roles'))
                                                <li><a href="{{ route('admin.roles.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> Roles & Permissions </a></li>
                                            @endif
                                            @if (Can::is_accessible('leaves'))
                                                <li><a href="{{ route('admin.leaves.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> leaves </a></li>
                                            @endif
                                            {{-- @if (Can::is_accessible('login_requests'))
                                                <li><a href="{{ route('admin.login_requests.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> Login Auth </a></li>
                                            @endif --}}
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if (Can::is_accessible('stores') || Can::is_accessible('store_user_mappings') || Can::is_accessible('store_user_module_mappings') || Can::is_accessible('store_kyc_mappings'))
                            <div class="col ">
                                <div class="p-3 h-100 d-flex hover-box">
                                    <div class="me-3 d-flex align-items-start">
                                        <i class="bx bx-store fs-1 "></i>
                                    </div>
                                    <div>
                                        <h6 class="custom-heading fw-bold">Manage Stores</h6>
                                        <ul class="list-unstyled mt-2">
                                            @if (Can::is_accessible('stores'))
                                                <li><a href="{{ route('admin.stores.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> All Store </a> </li>
                                            @endif
                                            @if (Can::is_accessible('store_user_mappings'))
                                                <li><a href="{{ route('admin.store_user_mappings.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> Store User Mapping </a> </li>
                                            @endif
                                            {{-- @if (Can::is_accessible('store_user_module_mappings'))
                                                <li><a href="{{ route('admin.store_user_module_mappings.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> Store User Module Mapping </a> </li>
                                            @endif --}}
                                            @if (Can::is_accessible('store_kyc_mappings'))
                                                <li><a href="{{ route('admin.store_kyc_mappings.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> Store Kyc Mapping </a> </li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if (Can::is_accessible('products') || Can::is_accessible('product_mappings'))
                            <div class="col ">
                                <div class="p-3 h-100 d-flex hover-box">
                                    <div class="me-3 d-flex align-items-start">
                                        <i class="bx bxs-contact fs-1 "></i>
                                    </div>
                                    <div>
                                        <h6 class="custom-heading fw-bold">Manage Products</h6>
                                        <ul class="list-unstyled mt-2">
                                            @if (Can::is_accessible('products'))
                                                <li><a href="{{ route('admin.products.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> All Products </a></li>
                                            @endif
                                            @if (Can::is_accessible('product_mappings'))
                                                <li><a href="{{ route('admin.product_mappings.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> Products Mapping </a></li>
                                            @endif
                                            @if (Can::is_accessible('brands'))
                                                <li><a href="{{ route('admin.brands.index', ['brand_type' => 'product_brand']) }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> Products Brand </a></li>
                                            @endif
                                            @if (Can::is_accessible('categories'))
                                                <li><a href="{{ route('admin.categories.index', ['category_type' => 'product_category']) }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> Products Category </a></li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if (Can::is_accessible('store_attendances') || Can::is_accessible('staff_attendances'))
                            <div class="col ">
                                <div class="p-3 h-100 d-flex hover-box">
                                    <div class="me-3 d-flex align-items-start">
                                        <i class="bx bx-user-check fs-1 "></i>
                                    </div>
                                    <div>
                                        <h6 class="custom-heading fw-bold">Manage Attendance </h6>
                                        <ul class="list-unstyled mt-2">
                                            @if (Can::is_accessible('store_attendances'))
                                                <li><a href="{{ route('admin.store_attendances.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> Store Attendance </a></li>
                                            @endif
                                            @if (Can::is_accessible('staff_attendances'))
                                                <li><a href="{{ route('admin.staff_attendances.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> Staff Attendance </a></li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if (Can::is_accessible('promotion_products') ||
                            Can::is_accessible('promotion_product_mappings') ||
                            Can::is_accessible('category_trackings') ||
                            Can::is_accessible('category_tracking_products') ||
                            Can::is_accessible('category_tracking_target_mappings'))

                            <div class="col ">
                                <div class="p-3 h-100 d-flex hover-box">
                                    <div class="me-3 d-flex align-items-start">
                                        <i class="bx bx-time fs-1 "></i>
                                    </div>
                                    <div>
                                        <h6 class="custom-heading fw-bold">Manage Tracking </h6>
                                        <ul class="list-unstyled mt-2">
                                            @if (Can::is_accessible('promotion_products'))
                                                <li><a href="{{ route('admin.promotion_products.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> Promotion Products </a></li>
                                            @endif
                                            @if (Can::is_accessible('promotion_product_mappings'))
                                                <li><a href="{{ route('admin.promotion_product_mappings.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> Promotion Product Mapping </a></li>
                                            @endif
                                            @if (Can::is_accessible('category_trackings'))
                                                <li><a href="{{ route('admin.category_trackings.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> Category Tracking </a></li>
                                            @endif
                                            @if (Can::is_accessible('category_tracking_products'))
                                                <li><a href="{{ route('admin.category_tracking_products.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> Category Tracking Product </a></li>
                                            @endif
                                            @if (Can::is_accessible('category_tracking_target_mappings'))
                                                <li><a href="{{ route('admin.category_tracking_target_mappings.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> Category Tracking Target Mapping </a></li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if (Can::is_accessible('competition_products') || Can::is_accessible('competition_product_mappings'))
                            <div class="col ">
                                <div class="p-3 h-100 d-flex hover-box">
                                    <div class="me-3 d-flex align-items-start">
                                        <i class="bx bx-line-chart fs-1 "></i>
                                    </div>
                                    <div>
                                        <h6 class="custom-heading fw-bold">Competition Benchmarking</h6>
                                        <ul class="list-unstyled mt-2">
                                            @if (Can::is_accessible('competition_products'))
                                                <li><a href="{{ route('admin.competition_products.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> Competition Product </a> </li>
                                            @endif
                                            @if (Can::is_accessible('competition_product_mappings'))
                                                <li><a href="{{ route('admin.competition_product_mappings.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> Competition Product Mapping </a> </li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if (Can::is_accessible('sale_targets'))
                            <div class="col ">
                                <div class="p-3 h-100 d-flex hover-box">
                                    <div class="me-3 d-flex align-items-start">
                                        <i class="bx bxs-discount fs-1 "></i>
                                    </div>
                                    <div>
                                        <h6 class="custom-heading fw-bold">Sales Targets</h6>
                                        <ul class="list-unstyled mt-2">
                                            <li><a href="{{ route('admin.sale_targets.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> Sales Targets </a> </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if (Can::is_accessible('posms') ||
                                Can::is_accessible('posm_store_mappings') ||
                                Can::is_accessible('posm_product_mappings'))
                            <div class="col ">
                                <div class="p-3 h-100 d-flex hover-box">
                                    <div class="me-3 d-flex align-items-start">
                                        <i class="bx bx-show fs-1 "></i>
                                    </div>
                                    <div>
                                        <h6 class="custom-heading fw-bold">Manage Visibility </h6>
                                        <ul class="list-unstyled mt-2">
                                            @if (Can::is_accessible('posms'))
                                                <li><a href="{{ route('admin.posms.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> Create POSM </a></li>
                                            @endif
                                            @if (Can::is_accessible('posm_store_mappings'))
                                                <li><a href="{{ route('admin.posm_store_mappings.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> POSM Store Mapping </a></li>
                                            @endif
                                            @if (Can::is_accessible('posm_product_mappings'))
                                                <li><a href="{{ route('admin.posm_product_mappings.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> POSM Product Mapping </a></li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if (Can::is_accessible('login_report') ||
                            Can::is_accessible('kyc_reports') ||
                            Can::is_accessible('sale_reports') ||
                            Can::is_accessible('visibility_reports') ||
                            Can::is_accessible('category_tracking_reports') ||
                            Can::is_accessible('promotion_tracking_reports') ||
                            Can::is_accessible('visitor_feedback_reports') ||
                            Can::is_accessible('competition_benchmarking_reports'))
                            <div class="col ">
                                <div class="p-3 h-100 d-flex hover-box">
                                    <div class="me-3 d-flex align-items-start">
                                        <i class="bx bxs-report fs-1 "></i>
                                    </div>
                                    <div>
                                        <h6 class="custom-heading fw-bold">Reports </h6>
                                        <ul class="list-unstyled mt-2">
                                            @if (Can::is_accessible('login_report'))
                                                <li><a href="{{ route('admin.login_requests.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> Login Report </a></li>
                                            @endif
                                            @if (Can::is_accessible('kyc_reports'))
                                                <li><a href="{{ route('admin.kyc_reports.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> Kyc Report </a></li>
                                            @endif
                                            @if (Can::is_accessible('sale_reports'))
                                                <li><a href="{{ route('admin.sale_reports.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> Sale Report </a></li>
                                            @endif
                                            @if (Can::is_accessible('visibility_reports'))
                                                <li><a href="{{ route('admin.visibility_reports.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> Visibility Report </a></li>
                                            @endif
                                            @if (Can::is_accessible('category_tracking_reports'))
                                                <li><a href="{{ route('admin.category_tracking_reports.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> Category Tracking Report </a></li>
                                            @endif
                                            @if (Can::is_accessible('promotion_tracking_reports'))
                                                <li><a href="{{ route('admin.promotion_tracking_reports.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> Promotion Tracking Report </a></li>
                                            @endif
                                            @if (Can::is_accessible('visitor_feedback_reports'))
                                                <li><a href="{{ route('admin.visitor_feedback_reports.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> Visitor Feedback Report </a></li>
                                            @endif
                                            @if (Can::is_accessible('competition_benchmarking_reports'))
                                                <li><a href="{{ route('admin.competition_benchmarking_reports.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> Comp. Benchmarking Report </a></li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if (Can::is_accessible('payout_clients') || Can::is_accessible('payout_stores'))
                            <div class="col ">
                                <div class="p-3 h-100 d-flex hover-box">
                                    <div class="me-3 d-flex align-items-start">
                                        <i class="bx bxs-credit-card fs-1 "></i>
                                    </div>
                                    <div>
                                        <h6 class="custom-heading fw-bold">Payout</h6>
                                        <ul class="list-unstyled mt-2">
                                            @if (Can::is_accessible('payout_clients'))
                                                <li><a href="{{ route('admin.payout_clients.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> Clients Payouts </a></li>
                                            @endif
                                            @if (Can::is_accessible('payout_stores'))
                                                <li><a href="{{ route('admin.payout_stores.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> Stores Payouts </a></li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if (Can::is_accessible('countries') || Can::is_accessible('states') || Can::is_accessible('cities') || Can::is_accessible('pin_codes'))
                            <div class="col ">
                                <div class="p-3 h-100 d-flex hover-box">
                                    <div class="me-3 d-flex align-items-start">
                                        <i class="bx bxs-map fs-1 "></i>
                                    </div>
                                    <div>
                                        <h6 class="custom-heading fw-bold">Manage Location </h6>
                                        <ul class="list-unstyled mt-2">
                                            @if (Can::is_accessible('countries'))
                                                <li><a href="{{ route('admin.countries.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> Countries </a></li>
                                            @endif
                                            @if (Can::is_accessible('states'))
                                                <li><a href="{{ route('admin.states.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> States </a></li>
                                            @endif
                                            @if (Can::is_accessible('cities'))
                                                <li><a href="{{ route('admin.cities.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> Cities </a></li>
                                            @endif
                                            @if (Can::is_accessible('pin_codes'))
                                                <li><a href="{{ route('admin.pin_codes.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> Pincodes </a></li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if (Can::is_accessible('system_settings') ||
                            Can::is_accessible('types') ||
                            Can::is_accessible('categories') ||
                            Can::is_accessible('brands') ||
                            Can::is_accessible('reasons') ||
                            Can::is_accessible('chains') ||
                            Can::is_accessible('banners') ||
                            Can::is_accessible('regions') ||
                            Can::is_accessible('formats') ||
                            Can::is_accessible('questions'))
                            <div class="col ">
                                <div class="p-3 h-100 d-flex hover-box">
                                    <div class="me-3 d-flex align-items-start">
                                        <i class="bx bx-carousel fs-1 "></i>
                                    </div>
                                    <div>
                                        <h6 class="custom-heading fw-bold">Config </h6>
                                        <ul class="list-unstyled mt-2">
                                            @if (Can::is_accessible('system_settings'))
                                                <li><a href="{{ route('admin.settings.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> General Config </a></li>
                                            @endif
                                            @if (Can::is_accessible('types'))
                                                <li><a href="{{ route('admin.types.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> Type Config </a></li>
                                            @endif
                                           @if (Can::is_accessible('chains'))
                                                <li><a href="{{ route('admin.chains.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> Chain Config </a></li>
                                            @endif
                                            @if (Can::is_accessible('categories'))
                                                <li><a href="{{ route('admin.categories.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> Category Config </a></li>
                                            @endif
                                            @if (Can::is_accessible('brands'))
                                                <li><a href="{{ route('admin.brands.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> Brands Config </a></li>
                                            @endif
                                            @if (Can::is_accessible('banners'))
                                                <li><a href="{{ route('admin.banners.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> Banners Config </a></li>
                                            @endif
                                            @if (Can::is_accessible('reasons'))
                                                <li><a href="{{ route('admin.reasons.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> Reason Config </a></li>
                                            @endif
                                            @if (Can::is_accessible('regions'))
                                                <li><a href="{{ route('admin.regions.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> Region Config </a></li>
                                            @endif
                                            @if (Can::is_accessible('formats'))
                                                <li><a href="{{ route('admin.formats.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> Format Config </a></li>
                                            @endif
                                            @if (Can::is_accessible('questions'))
                                                <li><a href="{{ route('admin.questions.index') }}" class="hover-link d-flex align-items-center rounded text-decoration-none text-dark link-primary">&#8226;</span> Question Config </a></li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('script')
    <script type="text/javascript">

        $(window).on('load', function() {
            $('div.simplebar-content-wrapper').removeAttr('style');
        });

        window.addEventListener('load', function () {
            document.querySelectorAll('.simplebar-content-wrapper').forEach(function(el) {
                el.style.setProperty('height', '100%', 'important');
                el.style.setProperty('overflow', 'hidden scroll', 'important');
            });
        });

        $(function() {
            $('body').addClass('sidebar-enable vertical-collpsed');
        });
    </script>
@endsection
