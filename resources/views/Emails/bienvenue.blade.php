<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Bienvenue sur Formation NéoVision</title>
</head>
<body style="margin:0; padding:0; background-color:#f3f6f9; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f6f9; padding:40px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="480" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:8px; overflow:hidden;">

                    {{-- En-tête --}}
                    <tr>
                        <td style="background-color:#405189; padding:24px 32px;">
                            <span style="color:#ffffff; font-size:20px; font-weight:600;">Formation NéoVision</span>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:32px;">
                            {{-- $name : nom saisi à l'inscription (échappé automatiquement par {{ }}) --}}
                            <p style="font-size:16px; color:#343a40; margin:0 0 16px;">Bonjour {{ $name }},</p>

                            <p style="font-size:15px; color:#495057; margin:0 0 16px; line-height:1.6;">
                                Bienvenue sur <strong>Formation NéoVision</strong> !
                                Votre inscription à la plateforme a été effectuée avec succès
                                et votre adresse email est désormais confirmée.
                            </p>

                            {{-- Récapitulatif du compte --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f6f9; border-radius:6px; margin:24px 0;">
                                <tr>
                                    <td style="padding:16px 20px; font-size:14px; color:#495057; line-height:1.9;">
                                        <strong>Type de compte :</strong> {{ $estEntreprise ? 'Entreprise' : 'Particulier' }}<br>
                                        {{-- La raison sociale n'existe que pour un compte entreprise --}}
                                        @if($estEntreprise && $raisonSociale)
                                            <strong>Entreprise :</strong> {{ $raisonSociale }}<br>
                                        @endif
                                        <strong>Adresse email :</strong> {{ $email }}<br>
                                        @if($dateInscription)
                                            <strong>Inscrit le :</strong> {{ $dateInscription }}
                                        @endif
                                    </td>
                                </tr>
                            </table>

                            {{-- Suite du parcours, adaptée au type de compte --}}
                            <p style="font-size:15px; color:#495057; margin:0 0 24px; line-height:1.6;">
                                @if($estEntreprise)
                                    Vous pouvez dès maintenant consulter notre catalogue pour trouver
                                    les formations adaptées aux besoins de votre entreprise.
                                @else
                                    Vous pouvez dès maintenant consulter notre catalogue, choisir
                                    la formation qui vous intéresse et vous inscrire à une classe.
                                @endif
                            </p>

                            {{-- Bouton vers le catalogue. route() génère l'URL complète à partir de APP_URL (.env). --}}
                            <div style="text-align:center; margin:32px 0;">
                                <a href="{{ route('cours.catalogue') }}"
                                   style="display:inline-block; background-color:#405189; color:#ffffff; text-decoration:none; font-size:15px; font-weight:600; padding:12px 28px; border-radius:6px;">
                                    Découvrir les formations
                                </a>
                            </div>

                            <p style="font-size:14px; color:#6c757d; margin:0; line-height:1.6;">
                                Si vous n'êtes pas à l'origine de cette inscription, vous pouvez ignorer cet email.
                            </p>
                        </td>
                    </tr>

                    {{-- Pied de page --}}
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