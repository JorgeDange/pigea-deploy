<!DOCTYPE html>
<html lang="pt">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Recebemos o seu registo — PIGEA</title></head>
<body style="margin:0; padding:0; background-color:#FFFFFF; font-family: Arial, Helvetica, sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#FFFFFF; padding:40px 20px;">
        <tr><td align="center">
            <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="max-width:600px; width:100%;">
                <tr><td align="center" style="padding:0 0 30px 0;"><img src="https://i.ibb.co/jvhNs1zv/logo.png" alt="PIGEA" width="150" style="display:block; width:150px; max-width:150px; height:auto; border:0;"></td></tr>
                <tr><td align="center" style="padding:0 0 20px 0;">
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0"><tr><td align="center" style="width:80px; height:80px; background-color:#F2A900; border-radius:50%;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    </td></tr></table>
                </td></tr>
                <tr><td align="center" style="padding:0 20px 15px 20px;"><h1 style="margin:0; font-size:24px; color:#1A1A1A; font-family: Georgia, serif;">Registo Recebido!</h1></td></tr>
                <tr><td align="center" style="padding:0 40px 30px 40px;">
                    @if ($tipo === 'empresa')
                        <p style="margin:0 0 20px 0; font-size:16px; line-height:1.6; color:#333333;">Obrigado — recebemos o registo da empresa <strong style="color:#C8102E;">{{ $nome }}</strong> no Diretório PIGEA.</p>
                    @else
                        <p style="margin:0 0 20px 0; font-size:16px; line-height:1.6; color:#333333;">Obrigado — recebemos os dados profissionais de <strong style="color:#C8102E;">{{ $nome }}</strong>.</p>
                    @endif
                    <p style="margin:0; font-size:16px; line-height:1.6; color:#333333;">O seu pedido está <strong>em processamento</strong>. A equipa PIGEA vai analisar a informação submetida e vai avisá-lo por email assim que houver decisão.</p>
                </td></tr>
                <tr><td style="padding:30px 40px; background-color:#F5F5F5; border-radius:8px;">
                    <p style="margin:0 0 12px 0; font-size:14px; line-height:1.6; color:#666666;"><strong>Não precisa de fazer mais nada.</strong> Guarde este email como comprovativo do pedido.</p>
                    <p style="margin:0; font-size:14px; line-height:1.6; color:#666666;">Se precisar de corrigir alguma informação, responda a este email ou escreva para <a href="mailto:geral@pigea.ao" style="color:#C8102E; text-decoration:underline;">geral@pigea.ao</a>.</p>
                </td></tr>
                <tr><td align="center" style="padding:30px 20px 0 20px;"><p style="margin:0 0 5px 0; font-size:12px; color:#999999;">© 2026 PIGEA — Plataforma de Impulsionamento das Geociências em Angola</p><p style="margin:0; font-size:12px; color:#999999;"><a href="https://pigea.ao" style="color:#C8102E; text-decoration:none;">pigea.ao</a></p></td></tr>
            </table>
        </td></tr>
    </table>
</body>
</html>
