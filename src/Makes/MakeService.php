<?php
namespace Codemax\Generator\Makes;

use Illuminate\Filesystem\Filesystem;
use Codemax\Generator\Commands\ScaffoldMakeCommand;
use Codemax\Generator\Validators\SchemaParser as ValidatorParser;
use Codemax\Generator\Validators\SyntaxBuilder as ValidatorSyntax;

class MakeService
{
    use MakerTrait;

    /**
     * Store name from Model
     *
     * @var ScaffoldMakeCommand
     */
    protected $scaffoldCommandObj;

    /**
     * Create a new instance.
     *
     * @param ScaffoldMakeCommand $scaffoldCommand
     * @param Filesystem $files
     * @return void
     */
    function __construct(ScaffoldMakeCommand $scaffoldCommand, Filesystem $files)
    {
        $this->files = $files;
        $this->scaffoldCommandObj = $scaffoldCommand;

        $this->start();
    }

    /**
     * Start make controller.
     *
     * @return void
     */
    private function start()
    {
        $model = $this->scaffoldCommandObj->getObjName('Name');
        $service_name = $model . 'Service';
        $this->makeService($service_name, 'service');
    }

    protected function makeService($name, $stubname)
    {
        $path = $this->getPath($name, 'service');

        if ($this->files->exists($path))
        {
            return $this->scaffoldCommandObj->comment("x " . $path);
        }

        $this->makeDirectory($path);

        $this->files->put($path, $this->compileStub($stubname));

        $this->scaffoldCommandObj->info('+ ' . $path);
    }

    protected function compileStub($filename)
    {
        $stub = $this->files->get(substr(__DIR__,0, -5) . 'Stubs/'.$filename.'.stub');

        $this->buildStub($this->scaffoldCommandObj->getMeta(), $stub);
        // $this->replaceValidator($stub);

        return $stub;
    }

    /*
    protected function registerService($model, $service_name)
    {
        $path = './app/Providers/AuthServiceProvider.php';
        $content = $this->files->get($path);

        if (strpos($content, $service_name) === false) {
            $content = str_replace(
                'policies = [',
                "policies = [\n\t\t \App\Models\\$model::class => \App\Policies\\$service_name::class,",
                $content
                );
            $this->files->put($path, $content);

            return $this->scaffoldCommandObj->info('+ ' . $path . ' (Atualizado)');
        }

        return $this->scaffoldCommandObj->comment("x " . $path . ' (Ignorado)');
    }
    */
}