#!/usr/bin/nodejs

var args = process.argv,
    email = args.splice(2),
    continuation = function (email) {
        'use strict';

        var nodemailer = require('nodemailer'),
            smtpTransport = require('nodemailer-smtp-transport'),
            transporter = nodemailer.createTransport(smtpTransport({
                host: 'mail.icmbio.gov.br',
                port: 25
            })),
            mailOptions = {
                from: 'warning-pizza@icmbio.gov.br',
                to: email,
                subject: 'Eih, vc ainda não pagou a PIZZA' + new Date(),
                html: '<h1>Aviso importante!</h1><p>Oh pessoa! Pq que ainda não pagastes a <b>PIZZA</b>?</p>'
            };

        transporter.sendMail(mailOptions, function (error, info) {
            if (error) {
                process.stderr.write('\n' + error + '\n');
                process.exit(1);
            } else {
                process.stdout.write('\nMensagem enviada!: ' + info.response + '\n');
                process.exit(0);
            }
        });
    };

if (!email.length) {
    var read = require('read');

    read({prompt: 'Informe o email:'}, function (error, email) {
        'use strict';

        if (error) {
            process.stderr.write('\n' + error + '\n');
            process.exit(1);
        }
        continuation(email);
    });
} else {
    continuation(email);
}
