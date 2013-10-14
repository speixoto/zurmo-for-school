<?php
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'lessc.inc.php');

class LessCompiler extends CApplicationComponent
{
    public $formatterName = 'compressed';

    protected $compiledCssPath;

    protected $lessFilesPath;

    protected $lessCompiler;

    protected $filesToCompile = array(
        'ie.less',
        'mobile.less',
        'newui.less'
    );

    public function init()
    {
        parent::init();
        $this->setLessCompiler($this->formatterName);
        $this->setCompiledCssPath();
        $this->setLessFilesPath();
    }

    public function setCompiledCssPath()
    {
        $themePath = Yii::app()->themeManager->getBasePath() . DIRECTORY_SEPARATOR . Yii::app()->theme->name;
        $this->compiledCssPath = $themePath . DIRECTORY_SEPARATOR . 'css';
    }

    public function getCompiledCssPath()
    {
        if (isset($this->compiledCssPath) && !empty($this->compiledCssPath))
        {
            return $this->compiledCssPath;
        }
        else
        {
            return null;
        }
    }

    public function setLessFilesPath()
    {
        $themePath = Yii::app()->themeManager->getBasePath() . DIRECTORY_SEPARATOR . Yii::app()->theme->name;
        $this->lessFilesPath = $themePath . DIRECTORY_SEPARATOR . 'less';
    }

    public function getLessFilesPath()
    {
        if (isset($this->lessFilesPath) && !empty($this->lessFilesPath))
        {
            return $this->lessFilesPath;
        }
        else
        {
            return null;
        }
    }

    public function setLessCompiler($formatterName)
    {
        $this->lessCompiler = new lessc;
        $this->lessCompiler->setPreserveComments(true);
        $this->lessCompiler->setFormatter($this->formatterName);
    }

    public function getLessCompiler()
    {
        if ($this->lessCompiler instanceOf lessc)
        {
            return $this->lessCompiler;
        }
        else
        {
            throw new NotSupportedException();
        }
    }

    public function compile()
    {
        if (is_array($this->filesToCompile) && !empty($this->filesToCompile))
        {
            foreach ($this->filesToCompile as $lessFile)
            {
                $lessFilePath = $this->getLessFilesPath() . DIRECTORY_SEPARATOR . $lessFile;
                $cssFileName = str_replace('less', 'css', $lessFile);
                $cssFilePath = $this->getCompiledCssPath() . DIRECTORY_SEPARATOR . $cssFileName;
                $this->getLessCompiler()->compileFile($lessFilePath, $cssFilePath);
            }
        }
    }


    /*
    // path to store compiled css files
    // defaults to 'application.assets.css'
    public $compiledPath=null;

    // compiled output formatter
    // accepted values: 'lessjs' , 'compressed' , 'classic'
    // defaults to 'lessjs'
    // read http://leafo.net/lessphp/docs/#output_formatting for details
    public $formatter='lessjs';

    // passing in true will cause the input to always be recompiled
    public $forceCompile=false;

    // if set to true, compileFile method will compile .less to .css ONLY if output .css file not found
    // otherwise compileFile method will only return path and filename of existing .css file
    // this mode is for production
    public $disabled=false;

    private $lessc=null;

    public function init()
    {
        if (!$this->compiledPath)
        {
            $this->compiledPath='application.assets.css';
        }

        $alias=YiiBase::getPathOfAlias($this->compiledPath);
        if ($alias)
        {
            $this->compiledPath=$alias;
        }
        elseif (!is_dir($this->compiledPath))
        {
            $this->compiledPath=Yii::app()->basePath.'/assets/css';
        }

        if ($this->formatter!='lessjs'&&$this->formatter!='compressed'&&$this->formatter!='classic')
        {
            $this->formatter='lessjs';
        }

        $this->lessc=new lessc;
        $this->lessc->setFormatter($this->formatter);
    }

    public function compileFile($file,$fileOut='',$useCompiledPath=true)
    {
        if (!$fileOut)
        {
            $fileOut=basename($file,'.less').'.css';
        }
        if ($useCompiledPath)
        {
            $fileOut=$this->compiledPath.'/'.$fileOut;
        }

        $compile=false;

        if (!$this->forceCompile&&!$this->disabled)
        {
            $files=Yii::app()->cache->get('less-compiler-'.$file.'-updated');
            if ($files&&is_array($files))
            {
                foreach ($files as $_file=>$_time)
                {
                    if (filemtime($_file)!=$_time)
                    {
                        $compile=true;
                        break;
                    }
                }
            }
            unset($files);
        }

        if (!file_exists($fileOut)||$compile||$this->forceCompile)
        {
            $cache=$this->lessc->cachedCompile($file);
            file_put_contents($fileOut,$cache['compiled']);
            Yii::app()->cache->set('less-compiler-'.$file.'-updated',$cache['files']);
        }

        return $fileOut;
    }

    public function compile($css)
    {
        if (is_file($css))
        {
            return $this->lessc->compileFile($css);
        }
        else
        {
            return $this->lessc->compile($css);
        }
    }
    */

}

?>
