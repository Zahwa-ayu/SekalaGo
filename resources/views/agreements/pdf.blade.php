<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $agreement->documentNumber() }}</title>
    <style>
        @page { margin: 55px 55px 70px 55px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; line-height: 1.55; color: #111; }
        .banner { border: 1px solid #b00; color: #b00; text-align: center; padding: 6px; margin-bottom: 14px; font-weight: bold; }
        .sign { width: 100%; margin-top: 28px; border-collapse: collapse; }
        .sign td { width: 50%; vertical-align: top; padding: 4px 8px; }
        .sigbox { border: 1px solid #999; height: 80px; padding: 8px; margin-top: 6px; }
        .typed { font-family: 'DejaVu Serif', serif; font-style: italic; font-size: 16px; }
        .muted { color: #666; }
        .meta { font-size: 8px; color: #555; margin-top: 4px; }
        .footer { position: fixed; bottom: -40px; left: 0; right: 0; font-size: 9px; color: #666; text-align: center; }
    </style>
</head>
<body>
    @if ($agreement->status !== 'signed')
        <div class="banner">DRAFT - BELUM DITANDATANGANI</div>
    @endif

    <div>{!! nl2br(e($agreement->content)) !!}</div>

    <table class="sign">
        <tr>
            <td>
                <strong>PIHAK PERTAMA</strong><br>
                {{ $platform }}
                <div class="sigbox">
                    <span class="muted">Diterbitkan secara elektronik<br>
                    {{ $agreement->sent_at?->format('d/m/Y H:i') }}</span>
                </div>
            </td>
            <td>
                <strong>PIHAK KEDUA</strong><br>
                {{ $partner->company_name }}
                <div class="sigbox">
                    @if ($agreement->status === 'signed')
                        <span class="typed">{{ $agreement->signed_name }}</span>
                        <div class="meta">
                            Ditandatangani secara elektronik<br>
                            {{ $agreement->signed_at->format('d/m/Y H:i:s') }} | IP {{ $agreement->signed_ip }}
                        </div>
                    @else
                        <span class="muted">(belum ditandatangani)</span>
                    @endif
                </div>
                @if ($agreement->signature_hash)
                    <div class="meta">
                        Sidik jari dokumen (SHA-256):<br>
                        {{ substr($agreement->signature_hash, 0, 32) }}<br>
                        {{ substr($agreement->signature_hash, 32) }}
                    </div>
                @endif
            </td>
        </tr>
    </table>

    <div class="footer">
        {{ $agreement->documentNumber() }} | Versi {{ $agreement->version }} |
        {{ $agreement->status === 'signed' ? 'Ditandatangani' : 'Draft' }}
    </div>
</body>
</html>