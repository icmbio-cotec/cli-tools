#!/usr/bin/php
<?php
/**
 * @author J. Augusto <augustowebd@gmail.com>
 * @version 0.0.1
 * */

# @todo incluir opcao de sobrescrita de arquivos
# @todo incluir opcao para informar parent (herança)
# @todo incluir opcao para definir a permissao do elementos criados

# cores usadas
define('BG_COLOR_DEFAULT', 'black',  FALSE);
define('FG_COLOR_DEFAULT', 'yellow', FALSE);
define('FG_COLOR_ERROR',   'red',    FALSE);

# faz a leitura do que foi digitado
$options = arguments($argv);
argument_validate($options);

# comandos aceitos
define('CMD_CREATE', 'create', FALSE);

# define a pasta onde o sial está localizado
define('SIAL_HOME', '/var/www/SSPCore', FALSE);

# define o diretorio com os templates das classes que serao criadas
define('CLASS_TEMPLATE_PATH',
    sprintf(
        '/var/www/SSPCore%2$sbr%2$sgov%2$ssial%2$stools%2$stemplate'
        , constant('SIAL_HOME')
        , DIRECTORY_SEPARATOR
    )
    , FALSE
);

# recupera o caminho atual
define('CURRENT_PATH', getcwd(), FALSE);

# determina o usuário do apache
$httpdUser = array();
exec('echo $(ps axho user,comm|grep -E "httpd|apache"|uniq|grep -v "root"|awk \'END {if ($1) print $1}\')', $httpdUser);
$httpdUser = current($httpdUser);
$httpdUser = isset($options['options']['httpduser']) ? $options['options']['httpduser'] :  trim($httpdUser);
if (empty($httpdUser)) {
    print_error("Não foi possível determinar o usuário apache\nInforme o param: --httpduser=<user>");
}

# define o usuario e grupo padrao, caso nao sejam informados
define('MODULE_DEFAULT_MODE', 0777, FALSE);
define('MODULE_DEFAULT_OWNER', get_current_user(), FALSE);
define('MODULE_DEFAULT_GROUP', $httpdUser, FALSE);

if (1 === sizeof($argv)) {
    usage();
    return;
}

# ativa criacao de estrutura
create_estruct($options);

function arguments ( $args )
{
  array_shift( $args );
  $endofoptions = false;

  $ret = array (
    'commands' => array(),
    'options' => array(),
    'flags'    => array(),
    'arguments' => array(),
    );

  while ( $arg = array_shift($args) )
  {

    // if we have reached end of options,
    //we cast all remaining argvs as arguments
    if ($endofoptions)
    {
      $ret['arguments'][] = $arg;
      continue;
    }

    // Is it a command? (prefixed with --)
    if ( substr( $arg, 0, 2 ) === '--' )
    {

      // is it the end of options flag?
      if (!isset ($arg[3]))
      {
        $endofoptions = true;; // end of options;
        continue;
      }

      $value = "";
      $com   = substr( $arg, 2 );

      // is it the syntax '--option=argument'?
      if (strpos($com,'='))
        list($com,$value) = split("=",$com,2);

      // is the option not followed by another option but by arguments
      elseif (strpos($args[0],'-') !== 0)
      {
        while (strpos($args[0],'-') !== 0)
          $value .= array_shift($args).' ';
        $value = rtrim($value,' ');
      }

      $ret['options'][$com] = !empty($value) ? $value : true;
      continue;

    }

    // Is it a flag or a serial of flags? (prefixed with -)
    if ( substr( $arg, 0, 1 ) === '-' )
    {
      for ($i = 1; isset($arg[$i]) ; $i++)
        $ret['flags'][] = $arg[$i];
      continue;
    }

    // finally, it is not option, nor flag, nor argument
    $ret['commands'][] = $arg;
    continue;
  }

  if (!count($ret['options']) && !count($ret['flags']))
  {
    $ret['arguments'] = array_merge($ret['commands'], $ret['arguments']);
    $ret['commands'] = array();
  }
    return $ret;
}

function argument_validate ($arguments)
{
    if (! sizeof($arguments['options'])) {
        usage();
        exit(1);
    }

    # valida se o nome do sistema foi informado
    if (! isset($arguments['options']['system'])) {
        print_error("O nome do sistema não foi informado.");
    }

    if (! isset($arguments['options']['module'])) {
        print_error("O nome do modulo não foi informado.");
    }

    if (! isset($arguments['options']['param'])) {
        print_error("Não informado qual parte do módulo deverá ser criado");
    }
}

function usage ()
{
    $color = new Colors();
    echo chr(27)."[H".chr(27)."[2J";
    echo $color->getColoredString("  SIAL Create Module - Vs 0.0.1                                                                                                          \n", "cyan",   constant('BG_COLOR_DEFAULT'));
    echo $color->getColoredString("    usage: create_module create --system=system_name --module=module_name --param=full  --viewtype=html --persisttype=database  | to create all parts of module \n", constant('FG_COLOR_DEFAULT'), constant('BG_COLOR_DEFAULT'));
    echo $color->getColoredString("    usage: create_module create --system=system_name --module=module_name --param=mvcb  --viewtype=html --persisttype=database  | to create only mvcb           \n", constant('FG_COLOR_DEFAULT'), constant('BG_COLOR_DEFAULT'));
    echo $color->getColoredString("    usage: create_module create --system=system_name --module=module_name --param=model                                         | to create only model          \n", constant('FG_COLOR_DEFAULT'), constant('BG_COLOR_DEFAULT'));
    echo $color->getColoredString("    usage: create_module create --system=system_name --module=module_name --param=view  --viewtype=html                         | to create only view           \n", constant('FG_COLOR_DEFAULT'), constant('BG_COLOR_DEFAULT'));
    echo $color->getColoredString("    usage: create_module create --system=system_name --module=module_name --param=controller                                    | to create only controller     \n", constant('FG_COLOR_DEFAULT'), constant('BG_COLOR_DEFAULT'));
    echo $color->getColoredString("    usage: create_module create --system=system_name --module=module_name --param=business                                      | to create only business       \n", constant('FG_COLOR_DEFAULT'), constant('BG_COLOR_DEFAULT'));
    echo $color->getColoredString("    usage: create_module create --system=system_name --module=module_name --param=persist   --persisttype=database              | to create only persist        \n", constant('FG_COLOR_DEFAULT'), constant('BG_COLOR_DEFAULT'));
    echo $color->getColoredString("    usage: create_module create --system=system_name --module=module_name --param=valueObject                                   | to create only valueObject    \n", constant('FG_COLOR_DEFAULT'), constant('BG_COLOR_DEFAULT'));
}

function print_error ($message)
{
    $color = new Colors();
    # @todo igualar o tamanho do frame colorido
    echo $color->getColoredString("\n[Error]\n",  constant('FG_COLOR_DEFAULT'), constant('BG_COLOR_DEFAULT'));
    echo $color->getColoredString("{$message}\n", constant('FG_COLOR_ERROR'),    constant('BG_COLOR_DEFAULT'));

    exit(1);
}

# @author http://www.if-not-true-then-false.com/2010/php-class-for-coloring-php-command-line-cli-scripts-output-php-output-colorizing-using-bash-shell-colors/
class Colors {
    private $foreground_colors = array();
    private $background_colors = array();

    public function __construct() {
        // Set up shell colors
        $this->foreground_colors['black'] = '0;30';
        $this->foreground_colors['dark_gray'] = '1;30';
        $this->foreground_colors['blue'] = '0;34';
        $this->foreground_colors['light_blue'] = '1;34';
        $this->foreground_colors['green'] = '0;32';
        $this->foreground_colors['light_green'] = '1;32';
        $this->foreground_colors['cyan'] = '0;36';
        $this->foreground_colors['light_cyan'] = '1;36';
        $this->foreground_colors['red'] = '0;31';
        $this->foreground_colors['light_red'] = '1;31';
        $this->foreground_colors['purple'] = '0;35';
        $this->foreground_colors['light_purple'] = '1;35';
        $this->foreground_colors['brown'] = '0;33';
        $this->foreground_colors['yellow'] = '1;33';
        $this->foreground_colors['light_gray'] = '0;37';
        $this->foreground_colors['white'] = '1;37';

        $this->background_colors['black'] = '40';
        $this->background_colors['red'] = '41';
        $this->background_colors['green'] = '42';
        $this->background_colors['yellow'] = '43';
        $this->background_colors['blue'] = '44';
        $this->background_colors['magenta'] = '45';
        $this->background_colors['cyan'] = '46';
        $this->background_colors['light_gray'] = '47';
    }

    // Returns colored string
    public function getColoredString ($string, $foreground_color = null, $background_color = null) {
        $colored_string = "";

        // Check if given foreground color found
        if (isset($this->foreground_colors[$foreground_color])) {
        $colored_string .= "\033[" . $this->foreground_colors[$foreground_color] . "m";
        }
        // Check if given background color found
        if (isset($this->background_colors[$background_color])) {
        $colored_string .= "\033[" . $this->background_colors[$background_color] . "m";
        }

        // Add string and end coloring
        $colored_string .=  $string . "\033[0m";

        return $colored_string;
    }

    // Returns all foreground color names
    public function getForegroundColors() {
        return array_keys($this->foreground_colors);
    }

    // Returns all background color names
    public function getBackgroundColors() {
        return array_keys($this->background_colors);
    }
}

function create_estruct ($arguments)
{
    $command = isset($arguments['commands']['0'])
             ?  $arguments['commands']['0']
             : NULL
             ;

    if (constant('CMD_CREATE') != $command) {
        // constant('CURRENT_PATH');
        print_error("Esta opção não está disponível no momento.");
    }

    if (! is_writeable(constant('CURRENT_PATH'))) {
        print_error("Não há permissão suficiente para criar arquivos");
    }

    if (! isset($arguments['options']['module'])) {
        print_error("O nome do módulo não foi informado");
    }

    $param = isset($arguments['options']['param'])
           ? $arguments['options']['param']
           : 'full'
           ;

    $basedir = constant('CURRENT_PATH') . DIRECTORY_SEPARATOR . $arguments['options']['module'];

    $system = $arguments['options']['system'];
    $module = $arguments['options']['module'];
    $class  = ucfirst($module);

    foreach (struct_skeleton($basedir, $arguments) as $target => $directory) {
        create_dir($directory);
        create_content($directory, $target, $system, $module, $class);
    }
}

/**
 * @param string $basedir diretorio alvo do modulo
 * @param string[] $arguments
 * @return string[][]
 */
function struct_skeleton ($basedir, $arguments)
{
    $modName     = $arguments['options']['module'];
    $option      = $arguments['options']['param'];
    $viewType    = isset($arguments['options']['viewtype']) ? $arguments['options']['viewtype'] : 'html';
    $persistType = isset($arguments['options']['persisttype']) ? $arguments['options']['persisttype'] : 'database';

    $mvcb        = $basedir . DIRECTORY_SEPARATOR . 'mvcb';
    $mvcb_m      = $mvcb    . DIRECTORY_SEPARATOR . 'model';
    $mvcb_v      = $mvcb    . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . $viewType;
    $mvcb_c      = $mvcb    . DIRECTORY_SEPARATOR . 'controller';
    $mvcb_b      = $mvcb    . DIRECTORY_SEPARATOR . 'business';
    $valueObject = $basedir . DIRECTORY_SEPARATOR . 'valueObject';
    $persist     = $basedir . DIRECTORY_SEPARATOR . 'persist' . DIRECTORY_SEPARATOR . $persistType;

    $struct = array();

    # requer o tipo da view o tipo de persistencia
    switch ($option) {
        case 'full':
            $struct['mvcb']        = $mvcb;
            $struct['mvcb_m']      = $mvcb_m;
            $struct['mvcb_v']      = $mvcb_v;
            $struct['mvcb_c']      = $mvcb_c;
            $struct['mvcb_b']      = $mvcb_b;
            $struct['valueObject'] = $valueObject;
            $struct['persist']     = $persist;
            break;

        case 'mvcb':
            $struct['mvcb']   = $mvcb;
            $struct['mvcb_m'] = $mvcb_m;
            $struct['mvcb_v'] = $mvcb_v;
            $struct['mvcb_c'] = $mvcb_c;
            $struct['mvcb_b'] = $mvcb_b;
            break;

        case 'model':
            $struct['mvcb_m'] = $mvcb_m;
            break;

        case 'view':
            $struct['mvcb_v'] = $mvcb_v;
            break;

        case 'controller':
            $struct['mvcb_c'] = $mvcb_c;
            break;

        case 'business':
            $struct['mvcb_b'] = $mvcb_b;
            break;

        case 'valueObject':
        case 'valueobject':
            $struct['valueObject'] = $valueObject;
            break;

        case 'persist':
            $struct['persist'] = $persist;
            break;
    }

    return $struct;
}

function create_dir ($directory)
{
    if (is_dir($directory)) {
        return true;
    }

    if (! mkdir($directory, constant('MODULE_DEFAULT_MODE'), TRUE)) {
        return FALSE;
    }

    clearstatcache($directory);

    # @todo arrumar solucao para permitir troca de grupo de subdiretorios
    return (
        # estes @s sairá tao logo uma solucao para o 'todo' acima for solucionado
        @chgrp($directory, constant('MODULE_DEFAULT_GROUP')) &&
        @chown($directory, constant('MODULE_DEFAULT_OWNER'))
    );
}

function create_content ($directory, $target, $system, $module, $class)
{
    $target($directory, $system, $module, $class);
}

# serve apenas para manter a compatibilidade
# de chamada no loop em 'create_content'
function mvcb ($directory, $system, $module, $class) { return true; }

function mvcb_m ($directory, $system, $module, $class) {
    $template = file_get_contents(constant('CLASS_TEMPLATE_PATH') . DIRECTORY_SEPARATOR . 'mvcb_m.tpl');
    $template = sprintf($template, $system, $module, $class);
    $target   = sprintf('%s%s%sModel.php'
                      , $directory
                      , DIRECTORY_SEPARATOR
                      , $class
                );

    if (! file_put_contents($target, $template)) {
        print_error("Não foi possível criar a Model");
    }
}

function mvcb_v ($directory, $system, $module, $class) { return true; }

function mvcb_c ($directory, $system, $module, $class) {
    $template = file_get_contents(constant('CLASS_TEMPLATE_PATH') . DIRECTORY_SEPARATOR . 'mvcb_c.tpl');
    $template = sprintf($template, $system, $module, $class);
    $target   = sprintf('%s%s%sController.php'
                      , $directory
                      , DIRECTORY_SEPARATOR
                      , $class
                );

    if (! file_put_contents($target, $template)) {
        print_error("Não foi possível criar a Controller");
    }
}

function mvcb_b ($directory, $system, $module, $class) {
    $template = file_get_contents(constant('CLASS_TEMPLATE_PATH') . DIRECTORY_SEPARATOR . 'mvcb_b.tpl');
    $template = sprintf($template, $system, $module, $class);
    $target   = sprintf('%s%s%sBusiness.php'
                      , $directory
                      , DIRECTORY_SEPARATOR
                      , $class
                );

    if (! file_put_contents($target, $template)) {
        print_error("Não foi possível criar a Business");
    }
}

function valueObject ($directory, $system, $module, $class) {
    $template = file_get_contents(constant('CLASS_TEMPLATE_PATH') . DIRECTORY_SEPARATOR . 'valueObject.tpl');
    $template = sprintf($template, $system, $module, $class);
    $target   = sprintf('%s%s%sValueObject.php'
                      , $directory
                      , DIRECTORY_SEPARATOR
                      , $class
                );

    if (! file_put_contents($target, $template)) {
        print_error("Não foi possível criar a ValueObject");
    }
}

function persist ($directory, $system, $module, $class) {
    $template = file_get_contents(constant('CLASS_TEMPLATE_PATH') . DIRECTORY_SEPARATOR . 'persist.tpl');
    $template = sprintf($template, $system, $module, $class);
    $target   = sprintf('%s%s%sPersist.php'
                      , $directory
                      , DIRECTORY_SEPARATOR
                      , $class
                );

    if (! file_put_contents($target, $template)) {
        print_error("Não foi possível criar a Persist");
    }
}

# sinaliza ao SO que tudo foi bem ;)
exit (0);
