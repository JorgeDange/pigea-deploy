<!DOCTYPE html>
<html lang="pt">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>{{ $artigo->title }} — PIGEA</title></head>
<body style="margin:0; padding:0; background-color:#FFFFFF; font-family: Arial, Helvetica, sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#FFFFFF; padding:40px 20px;">
        <tr><td align="center">
            <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="max-width:600px; width:100%;">
                <tr><td align="center" style="padding:0 0 30px 0;"><img src="https://docs.pigea.ao/imag/logo.png" alt="PIGEA" width="150" style="display:block; width:150px; max-width:150px; height:auto; border:0;"></td></tr>

                @if ($coverUrl)
                <tr><td align="center" style="padding:0 0 20px 0; border-radius:10px; overflow:hidden;">
                    <img src="{{ $coverUrl }}" alt="{{ $artigo->image_alt ?: $artigo->title }}" width="600" style="display:block; width:100%; max-width:600px; height:auto; border:0; border-radius:10px;">
                </td></tr>
                @endif

                <tr><td style="padding:0 10px;">
                    <p style="margin:0 0 6px 0; font-size:12px; font-weight:bold; letter-spacing:1px; text-transform:uppercase; color:#C8102E;">Novo artigo PIGEA</p>
                    <h1 style="margin:0 0 14px 0; font-size:22px; line-height:1.3; color:#1A1A1A; font-family: Georgia, serif;">{{ $artigo->title }}</h1>
                    @if ($artigo->excerpt)
                        <p style="margin:0 0 22px 0; font-size:15px; line-height:1.6; color:#4A4A4A;">{{ $artigo->excerpt }}</p>
                    @endif
                </td></tr>

                <tr><td align="center" style="padding:10px 0 30px 0;">
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0"><tr><td align="center" style="background-color:#C8102E; border-radius:30px;">
                        <a href="{{ $lerUrl }}" style="display:inline-block; padding:16px 48px; font-size:16px; font-weight:bold; color:#FFFFFF; text-decoration:none; font-family:Arial, sans-serif;">Ler no site &rarr;</a>
                    </td></tr></table>
                </td></tr>

                <tr><td style="padding:24px 30px; background-color:#F5F5F5; border-radius:8px;">
                    <p style="margin:0 0 10px 0; font-size:14px; line-height:1.6; color:#666666;"><strong>Estás a receber este email</strong> porque subscreveu a newsletter do PIGEA.</p>
                    <p style="margin:0; font-size:14px; line-height:1.6; color:#666666;">Se já lá não quiser receber, pode <a href="{{ $unsubscribeUrl }}" style="color:#C8102E; text-decoration:underline;">cancelar a sua subscrição</a> a qualquer momento.</p>
                </td></tr>

                <tr><td align="center" style="padding:30px 20px 0 20px;"><p style="margin:0 0 5px 0; font-size:12px; color:#999999;">© 2026 PIGEA — Plataforma de Impulsionamento das Geociências em Angola</p><p style="margin:0; font-size:12px; color:#999999;"><a href="https://pigea.ao" style="color:#C8102E; text-decoration:none;">pigea.ao</a></p></td></tr>
            </table>
        </td></tr>
    </table>
</body>
</html>
