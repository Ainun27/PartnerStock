@extends('layouts.portal')

@section('title', 'Masuk Portal Mitra')

@section('content')
<div class="max-w-md mx-auto space-y-5 pt-2">

    <!-- Header Dongker Sesuai Tema Navbar -->
    <div class="bg-[#0f172a] text-white rounded-2xl p-5 text-center shadow-sm border border-slate-800">
        <h1 class="font-heading text-xl font-black text-white tracking-tight">Portal Warung Mitra</h1>
    </div>

    <!-- Alert Messages -->
    @if(session('info'))
        <div class="p-3.5 rounded-2xl bg-white border border-slate-200 text-slate-700 text-xs shadow-md">
            {{ session('info') }}
        </div>
    @endif

    @if($errors->any())
        <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold">
            {{ $errors->first() }}
        </div>
    @endif

    <!-- Form Login PIN Bersih -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-7 space-y-4 shadow-xl">
        <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider pb-2 border-b border-slate-100">
            Masuk dengan PIN
        </h2>

        <form method="POST" action="{{ route('portal.auth') }}" class="space-y-4 text-xs" autocomplete="off">
            @csrf

            @php
                $preselectedId = old('warung_id');
            @endphp

            <div>
                <label for="warung_id" class="block font-bold text-slate-700 mb-1">Nama Warung</label>
                <select name="warung_id" 
                        id="warung_id" 
                        required 
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs text-slate-900 outline-none focus:border-amber-500 font-bold bg-slate-50 focus:bg-white shadow-2xs">
                    <option value="" disabled {{ !$preselectedId ? 'selected' : '' }}>-- Pilih Warung Anda --</option>
                    @foreach($warungs as $w)
                        <option value="{{ $w->id }}" {{ $preselectedId == $w->id ? 'selected' : '' }}>
                            {{ $w->nama_warung }} ({{ $w->nama_pemilik }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="pin" class="block font-bold text-slate-700 mb-1">PIN Warung</label>
                <input type="password" 
                       id="pin" 
                       name="pin" 
                       required 
                       placeholder="Masukkan PIN Anda"
                       maxlength="10"
                       inputmode="numeric"
                       pattern="[0-9]*"
                       autocomplete="off"
                       data-lpignore="true"
                       data-form-type="other"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-mono tracking-widest outline-none focus:border-amber-500 text-slate-900 bg-slate-50 focus:bg-white shadow-2xs">
            </div>

            <button type="submit" class="app-btn-primary w-full py-2.5 text-xs font-bold justify-center shadow-xs cursor-pointer">
                Masuk ke Portal
            </button>
        </form>
    </div>

</div>
@endsection
