#!/usr/bin/nodejs

var colors = require('colors');

process.stdout.write( ('\nAdiciona um Atom Package fora do ' + 'apm'.red + '\n').bold );

var fail = function(text) {
    process.stderr.write(('\n' + text + '\n').red);
    process.exit(1);
};

var args = process.argv;

var read = require('read');

read({prompt:'Qual é a URL do repositório GitHub:'}, function(error, githubURL){
    if (error) {
        fail(error);
    }

    var pack = githubURL.replace(/^.*\/(.*$)/,'$1');

    var cmds = [
        'echo "cd ~/.atom/packages"',
              'cd ~/.atom/packages',

        'echo "git clone ' + githubURL + '"',
              'git clone ' + githubURL,

        'echo "cd ' + pack + '"',
              'cd ' + pack,

        'echo "npm install"',
              'npm install'
    ];

    process.stdout.write( ('\nVai executar:\n'.inverse + cmds.join('\n') + '\n').green );

    var textDefault = 'S';

    read({prompt:'Confirma?', default: textDefault}, function(error, yes){
        if (error) {
            fail(error);
        }
        if (yes == textDefault || yes.toUpperCase() == 'S') {
            var exec =  require('child_process').exec;
            exec(cmds.join(' && '), function(error, stdout, stderr) {
                if (stdout) {
                    process.stdout.write( ('\nSaída padrão:\n'.inverse + stdout).blue );
                }

                if (stderr) {
                    process.stderr.write( ('\nErro padrão:\n'.inverse + stderr).yellow );
                }

                if (error !== null) {
                    fail(error);
                }

                process.stdout.write('\nTerminou ' + '✓'.green + '\n'.cyan);
                process.exit(0);
            });

        } else {
            fail('Ok, então deixa queto!');
        }
    });
});
