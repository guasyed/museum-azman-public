<!DOCTYPE html>
<html lang="en">
<body style="margin:0;background:#f4f4f3;font-family:Arial,sans-serif;color:#18181b">
<div style="max-width:680px;margin:0 auto;padding:32px 20px">
    <div style="background:#080808;color:#fff;padding:24px"><h1 style="margin:0;font:normal 30px Georgia,serif">New Contact Enquiry</h1></div>
    <div style="background:#fff;padding:28px;border:1px solid #e4e4e7">
        <table style="width:100%;border-collapse:collapse">
            <tr><th style="padding:9px 12px;text-align:left;border-bottom:1px solid #eee;width:30%">Name</th><td style="padding:9px 12px;border-bottom:1px solid #eee">{{ $contactMessage->name }}</td></tr>
            <tr><th style="padding:9px 12px;text-align:left;border-bottom:1px solid #eee">Email</th><td style="padding:9px 12px;border-bottom:1px solid #eee">{{ $contactMessage->email }}</td></tr>
            <tr><th style="padding:9px 12px;text-align:left;border-bottom:1px solid #eee">Subject</th><td style="padding:9px 12px;border-bottom:1px solid #eee">{{ $contactMessage->subject ?: 'General enquiry' }}</td></tr>
        </table>
        <h3 style="margin-top:24px">Message</h3>
        <p style="white-space:pre-line;line-height:1.65">{{ $contactMessage->message }}</p>
        <p style="margin-top:26px;color:#71717a;font-size:13px">Reply directly to this email to respond to {{ $contactMessage->name }}.</p>
    </div>
</div>
</body>
</html>
