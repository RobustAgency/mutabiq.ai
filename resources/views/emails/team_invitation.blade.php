<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Team Invitation</title>
</head>
<body style="margin:0; padding:0; font-family: Arial, sans-serif; background-color:#f9fafb; color:#333;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f9fafb; padding:20px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="background:#ffffff; border-radius:8px; padding:30px;">
                    <!-- Header -->
                    <tr>
                        <td align="center" style="padding-bottom:20px;">
                            <h1 style="margin:0; font-size:24px; color:#111;">👋 You’re Invited!</h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="font-size:16px; line-height:24px; padding-bottom:20px;">
                            Hello,<br><br>
                            <strong>{{ $invitation->invitedBy->name }}</strong> has invited you to join 
                            <strong>{{ $invitation->organization->name }}</strong> as a 
                            <strong style="color:#2563eb;">{{ $invitation->role->name }}</strong>.
                        </td>
                    </tr>

                    <!-- Action Button -->
                    <tr>
                        <td align="center" style="padding: 20px 0;">
                            <a href="{{ $signupUrl }}" 
                               style="background-color:#16a34a; color:#ffffff; padding:12px 24px; text-decoration:none; 
                                      font-size:16px; font-weight:bold; border-radius:6px; display:inline-block;">
                                Accept Invitation
                            </a>
                        </td>
                    </tr>

                    <!-- Expiry Info -->
                    <tr>
                        <td style="font-size:14px; line-height:20px; color:#555; padding-top:10px;">
                            This invitation will expire on 
                            <strong>{{ $invitation->expires_at->format('M d, Y') }}</strong>.
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="font-size:14px; color:#777; padding-top:30px; border-top:1px solid #e5e7eb;">
                            If you weren’t expecting this invitation, you can safely ignore this email.<br><br>
                            — The {{ config('app.name') }} Team
                        </td>
                    </tr>

                    <!-- Fallback Link -->
                    <tr>
                        <td style="font-size:12px; color:#999; padding-top:20px;">
                            Having trouble with the button? Copy and paste this link into your browser:<br>
                            <a href="{{ $signupUrl }}" style="color:#2563eb;">{{ $signupUrl }}</a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
