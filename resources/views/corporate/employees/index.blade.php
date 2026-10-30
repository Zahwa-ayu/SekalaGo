@extends('layouts.app')

@section('content')
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.employees') }}</h1>

            <a href="{{ route('corporate.employees.create') }}"
               class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-sagara-blue to-sagara-teal hover:from-sagara-navy hover:to-sagara-blue rounded-xl shadow-md transition">
                <i class="fa-solid fa-user-plus"></i> {{ __('ui.add_employee') }}
            </a>
        </div>

        <div class="bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="px-6 py-4 font-bold">{{ __('ui.full_name') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.email') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.department') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.position') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.role') }}</th>
                            <th class="px-6 py-4 font-bold">{{ __('ui.status') }}</th>
                            <th class="px-6 py-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($employees as $employee)
                            <tr class="hover:bg-sky-50/50 transition">
                                <td class="px-6 py-4 font-bold text-slate-900">{{ $employee->displayName() }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $employee->user->email }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $employee->department ?? '-' }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $employee->position?->name ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 text-[11px] font-bold rounded-full whitespace-nowrap {{ $employee->role_in_company === 'admin_finance' ? 'bg-sky-100 text-sagara-blue' : 'bg-slate-100 text-slate-600' }}">
                                        {{ __('ui.role_' . $employee->role_in_company) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 text-[11px] font-bold rounded-full whitespace-nowrap {{ $employee->user->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $employee->user->is_active ? __('ui.active') : __('ui.inactive') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('corporate.employees.edit', $employee) }}"
                                       class="px-3 py-1.5 text-xs font-bold border border-slate-300 text-slate-600 hover:bg-slate-100 rounded-lg transition whitespace-nowrap">
                                        <i class="fa-solid fa-pen"></i> {{ __('ui.edit') }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection