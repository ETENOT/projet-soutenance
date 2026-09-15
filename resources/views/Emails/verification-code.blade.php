<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Code de vérification</title>
</head>
<body style="margin:0; padding:0; background-color:#f3f6f9; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f6f9; padding:40px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="480" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:8px; overflow:hidden;">
                    <tr>
                        <td style="background-color:#405189; padding:24px 32px;">
                            <span style="color:#ffffff; font-size:20px; font-weight:600;">Formation NéoVision</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <p style="font-size:16px; color:#343a40; margin:0 0 16px;">Bonjour {{ $name }},</p>
                            <p style="font-size:15px; color:#495057; margin:0 0 24px; line-height:1.6;">
                                Merci de votre inscription. Voici votre code de vérification pour confirmer votre adresse email :
                            </p>
                            <div style="text-align:center; margin:32px 0;">
                                <span style="display:inline-block; font-size:32px; letter-spacing:10px; font-weight:700; color:#405189; background-color:#f3f6f9; padding:16px 24px; border-radius:6px;">
                                    {{ $code }}
                                </span>
                            </div>
                            <p style="font-size:14px; color:#6c757d; margin:0 0 8px; line-height:1.6;">
                                Ce code est valable pendant <strong>5 minutes</strong>. Si vous n'avez pas demandé ce code, ignorez cet email.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#f3f6f9; padding:16px 32px; text-align:center;">
                            <span style="font-size:12px; color:#adb5bd;">&copy; {{ date('Y') }} Formation NéoVision</span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>