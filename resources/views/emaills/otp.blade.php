<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Code de vérification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 30px;
            border: 1px solid #ddd;
        }
        .otp-box {
            background-color: #3498db;
            color: white;
            font-size: 32px;
            font-weight: bold;
            text-align: center;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            letter-spacing: 5px;
        }
        .footer {
            background-color: #ecf0f1;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #7f8c8d;
            border-radius: 0 0 5px 5px;
        }
        .warning {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 10px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>SGWC</h1>
        <p>Vérification de votre compte</p>
    </div>
    
    <div class="content">
        <p>Bonjour <strong>{{ $name }}</strong>,</p>
        
        <p>Merci de vous être inscrit sur notre plateforme. Pour finaliser la création de votre compte, veuillez utiliser le code de vérification ci-dessous :</p>
        
        <div class="otp-box">
            {{ $otp }}
        </div>
        
        <div class="warning">
            <strong>⚠️ Important :</strong>
            <ul style="margin: 5px 0;">
                <li>Ce code est valable pendant <strong>10 minutes</strong></li>
                <li>Ne partagez ce code avec personne</li>
                <li>Si vous n'êtes pas à l'origine de cette demande, ignorez ce message</li>
            </ul>
        </div>
        
        <p>Si vous rencontrez des difficultés, n'hésitez pas à nous contacter.</p>
        
        <p>Cordialement,<br>
        <strong>L'équipe SGWC</strong></p>
    </div>
    
    <div class="footer">
        <p>© {{ date('Y') }} SGWC. Tous droits réservés.</p>
        <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
    </div>
</body>
</html>