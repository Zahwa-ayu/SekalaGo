@extends('layouts.app')

@section('content')
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="flex items-center justify-between mb-6 gap-4">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.companies') }}</h1>
            <a href="{{ route('admin.companies.create') }}"
               class="px-5 py-2.5 text-sm font-semibold text-white bg-sagara-blue hover:bg-sagara-navy rounded-xl shadow-md transition whitespace-nowrap">
                <i class="fa-solid fa-plus mr-1"></i> {{ __('ui.add_company') }}
            </a>
        </div>

        <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="px-6 py-4 font-bold">{{ __('ui.company_name') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.company_code') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.deposit_balance') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.default_limit') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.employees_count') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($companies as $company)
                            <tr class="hover:bg-sky-50/50 transition">
                                <td class="px-6 py-4 font-bold text-slate-900">{{ $company->company_name }}</td>
                                <td class="px-6 py-4 text-slate-600 font-mono text-xs">{{ $company->corporate_code }}</td>
                                <td class="px-6 py-4 text-slate-600 whitespace-nowrap">{{ format_price($company->deposit_balance, 'IDR') }}</td>
                                <td class="px-6 py-4 text-slate-600 whitespace-nowrap">{{ format_price($company->travel_policy_limit, 'IDR') }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-slate-100 text-slate-600">{{ $company->company_users_count }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs font-bold">
                                    <a href="{{ route('admin.companies.show', $company) }}" class="text-sagara-blue hover:underline">
                                        {{ __('ui.review') }} <i class="fa-solid fa-chevron-right ml-1"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                    <i class="fa-solid fa-building text-3xl mb-2 block"></i>
                                    {{ __('ui.no_companies') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection