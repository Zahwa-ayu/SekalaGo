@extends('layouts.app')

@section('content')
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="flex items-center justify-between mb-6 gap-4">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ __('ui.employees') }}</h1>

            <a href="{{ route('corporate.employees.create') }}"
               class="px-5 py-2.5 text-sm font-semibold text-white bg-sagara-blue hover:bg-sagara-navy rounded-xl shadow-md transition whitespace-nowrap">
                <i class="fa-solid fa-user-plus mr-1"></i> {{ __('ui.add_employee') }}
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
                            <th class="px-6 py-4 font-bold">{{ __('ui.actions') }}</th>
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
                                <td class="px-6 py-4 whitespace-nowrap text-xs font-bold">
                                    <a href="{{ route('corporate.employees.edit', $employee) }}" class="text-sagara-blue hover:underline">
                                        {{ __('ui.edit') }}
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