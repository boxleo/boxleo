<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Boxleo Courier HRMS</title>
    <style>
        /* Your existing styles go here... */

        /* Custom styles for the modified content */
        .intro {
            color: #333;
            font-size: 18px;
            margin-bottom: 20px;
        }

        .login-details {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .login-details strong {
            color: #3498db;
        }

        .cta-button {
            margin-top: 20px;
        }
    </style>
</head>

<body class="">
    <!-- Your existing HTML structure... -->

    <tr>
        <td class="wrapper">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td>
                        <h1>Welcome, {{ $user->firstname }}!</h1>
                        <p class="intro">
                            You are now part of the Boxleo Courier HRMS family. Get ready to experience a seamless and
                            efficient work environment.
                        </p>
                        <div class="login-details">
                            <p>
                                Your login details are as follows:
                            </p>
                            <p>
                                <strong>Email:</strong> {{ $user->email }}<br>
                                <strong>Password:</strong> {{ $password }}<br>
                            </p>
                        </div>
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0"
                            class="btn btn-primary cta-button">
                            <tbody>
                                <tr>
                                    <td align="left">
                                        <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                            <tbody>
                                                <tr>
                                                    <td><a href="https://hrm.boxleocourier.com/login" target="_blank">
                                                        Go to Portal</a>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <p>We are excited to have you on board and look forward to achieving great milestones together.
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- Your existing HTML structure... -->

</body>

</html>
