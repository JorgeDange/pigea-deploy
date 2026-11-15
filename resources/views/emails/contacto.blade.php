<!DOCTYPE html>
<html lang="pt" xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="x-apple-disable-message-reformatting">
<!--[if mso]>
<noscript><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript>
<![endif]-->
<title>Nova mensagem do site PIGEA</title>
<style type="text/css">
  @media only screen and (max-width: 620px) {
    .container { width: 96% !important; }
    .pad-body { padding: 22px 18px !important; }
    .pad-head { padding: 20px 18px !important; }
    .h1 { font-size: 22px !important; }
  }
</style>
</head>
<body style="margin:0; padding:0; background-color:#F5F5F5; font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; -webkit-font-smoothing:antialiased; color:#1A1A1A;">

<!-- preheader (oculto) -->
<span style="display:none; max-height:0; overflow:hidden; mso-hide:all;">Nova mensagem de contacto enviada por {{ $nome }} através do site PIGEA.</span>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#F5F5F5;">
  <tr>
    <td align="center" style="padding: 28px 12px;">

      <table role="presentation" class="container" width="600" cellpadding="0" cellspacing="0" style="max-width:600px; width:600px; background:#FFFFFF; border:1px solid #E0E0E0; border-radius:8px; overflow:hidden;">

        <!-- Cabeçalho escuro + barra vermelha -->
        <tr>
          <td class="pad-head" style="background:#1A1A1A; padding: 26px 30px 20px; text-align:left;">
            <img src="https://i.ibb.co/jvhNs1zv/logo.png" alt="PIGEA — Plataforma de Impulsionamento das Geociências em Angola" width="150" style="display:block; width:150px; max-width:150px; height:auto; border:0;">
            <div style="border-bottom: 3px solid #C8102E; line-height:3px; font-size:3px; margin: 18px 0 0;">&nbsp;</div>
          </td>
        </tr>

        <!-- Corpo -->
        <tr>
          <td class="pad-body" style="padding: 28px 30px 26px;">

            <h1 class="h1" style="margin:0 0 6px; font-family:Georgia,'Times New Roman',serif; font-size:26px; line-height:1.25; color:#1A1A1A; font-weight:700;">Nova mensagem do site</h1>
            <p style="margin:0 0 18px; font-size:13px; color:#666666;">Recebida através do formulário de contacto de <strong>pigea.ao</strong></p>

            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #E0E0E0; border-radius:6px; overflow:hidden; margin:0 0 18px;">
              <tr>
                <td style="width:96px; background:#F9F9F9; padding:12px 14px; font-size:12px; font-weight:700; color:#C8102E; text-transform:uppercase; letter-spacing:0.6px; border-bottom:1px solid #E0E0E0; vertical-align:top;">Nome</td>
                <td style="padding:12px 14px; font-size:14px; color:#1A1A1A; border-bottom:1px solid #E0E0E0;">{{ $nome }}</td>
              </tr>
              <tr>
                <td style="background:#F9F9F9; padding:12px 14px; font-size:12px; font-weight:700; color:#C8102E; text-transform:uppercase; letter-spacing:0.6px; vertical-align:top;">Email</td>
                <td style="padding:12px 14px; font-size:14px;"><a href="mailto:{{ $email }}" style="color:#C8102E; text-decoration:none; font-weight:600;">{{ $email }}</a></td>
              </tr>
            </table>

            <p style="margin:0 0 8px; font-size:12px; font-weight:700; color:#C8102E; text-transform:uppercase; letter-spacing:0.6px;">Mensagem</p>
            <div style="background:#F9F9F9; border:1px solid #E0E0E0; border-left:4px solid #C8102E; border-radius:6px; padding:14px 16px; font-size:14px; line-height:1.6; color:#1A1A1A; white-space:pre-wrap; word-wrap:break-word;">{{ $mensagem }}</div>

            <p style="margin:18px 0 0; font-size:12px; color:#666666; line-height:1.6;">
              Podes responder <strong>diretamente a este email</strong> — a resposta vai para
              <a href="mailto:{{ $email }}" style="color:#C8102E; text-decoration:none;">{{ $email }}</a>.
            </p>

          </td>
        </tr>

        <!-- Rodapé -->
        <tr>
          <td style="background:#1A1A1A; padding:18px 30px; text-align:center;">
            <p style="margin:0 0 6px; font-family:Georgia,'Times New Roman',serif; font-size:14px; font-weight:700; color:#FFFFFF;">PIGEA</p>
            <p style="margin:0 0 10px; font-size:10px; color:#BDBDBD; letter-spacing:0.4px;">PLATAFORMA DE IMPULSIONAMENTO DAS GEOCIÊNCIAS EM ANGOLA</p>
            <p style="margin:0; font-size:11px;">
              <a href="{{ config('app.url') }}" style="color:#F2A900; text-decoration:none; font-weight:600;">pigea.ao</a>
              &nbsp;·&nbsp;
              <a href="{{ url('/contactos') }}" style="color:#F2A900; text-decoration:none;">Contactos</a>
            </p>
            <p style="margin:10px 0 0; font-size:10px; color:#8A8A8A;">&copy; {{ date('Y') }} PIGEA — Todos os direitos reservados.</p>
          </td>
        </tr>

      </table>
      <p style="margin:14px 0 0; font-size:10px; color:#9A9A9A;">Email transacional de pigea.ao</p>

    </td>
  </tr>
</table>
</body>
</html>
