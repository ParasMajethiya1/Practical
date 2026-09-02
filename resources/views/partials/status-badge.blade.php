@php
    $styles = [
        "PENDING" => "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200",
        "SUCCESS" => "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200",
        "FAILED" => "bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200",
    ];
    $dots = [
        "PENDING" => "bg-amber-500",
        "SUCCESS" => "bg-emerald-500",
        "FAILED" => "bg-rose-500",
    ];
@endphp
<span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold {{ $styles[$status] ?? "bg-slate-100 text-slate-600 ring-1 ring-inset ring-slate-200" }}">
    <span class="h-1.5 w-1.5 rounded-full {{ $dots[$status] ?? "bg-slate-400" }} {{ $status === "PENDING" ? "animate-pulse" : "" }}"></span>
    {{ $status }}
</span>
