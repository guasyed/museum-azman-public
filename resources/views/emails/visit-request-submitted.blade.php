<!DOCTYPE html>
<html lang="en">
<body style="margin:0;background:#f4f4f3;font-family:Arial,sans-serif;color:#18181b">
<div style="max-width:680px;margin:0 auto;padding:32px 20px">
    <div style="background:#080808;color:#fff;padding:24px"><h1 style="margin:0;font:normal 30px Georgia,serif">New Visit Request</h1></div>
    <div style="background:#fff;padding:28px;border:1px solid #e4e4e7">
        <p>A new visitor registration has been submitted through the Museum Azman website.</p>
        <table style="width:100%;border-collapse:collapse">
            @foreach([
                'Name' => $visitRequest->name,
                'Email' => $visitRequest->email,
                'Phone' => $visitRequest->phone,
                'Occupation' => $visitRequest->occupation,
                'Company' => $visitRequest->company,
                'City' => $visitRequest->city,
                'Purpose' => $visitRequest->purpose,
                'Category' => $visitRequest->category,
                'Preferred Date' => optional($visitRequest->preferred_date)->format('d M Y'),
                'Guests' => $visitRequest->guests,
                'Source' => $visitRequest->source,
                'Social' => $visitRequest->social ?: '—',
            ] as $label => $value)
                <tr><th style="padding:9px 12px;text-align:left;border-bottom:1px solid #eee;width:34%">{{ $label }}</th><td style="padding:9px 12px;border-bottom:1px solid #eee">{{ $value }}</td></tr>
            @endforeach
        </table>
        @if($visitRequest->message)<h3>Message</h3><p style="white-space:pre-line">{{ $visitRequest->message }}</p>@endif
        @if($visitRequest->preferences)<h3>Preferences</h3><p>{{ implode(', ', $visitRequest->preferences) }}</p>@endif
    </div>
</div>
</body>
</html>
