<?php
namespace Codemax\Generator\Makes;

use Illuminate\Filesystem\Filesystem;
use Codemax\Generator\Commands\ScaffoldMakeCommand;
use Codemax\Generator\Validators\SchemaParser as ValidatorParser;
use Codemax\Generator\Validators\SyntaxBuilder as ValidatorSyntax;

class MakeRepository
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
        $repository_name = $model . 'Repository';
        $this->makeRepository($repository_name, 'repository');

        $this->bindRepository($model, $repository_name);
    }

    protected function makeRepository($name, $stubname)
    {
        $path = $this->getPath($name, 'repository');
        if ($this->files->exists($path))
        {
            return $this->scaffoldCommandObj->comment("x " . $path);
        }

        $this->makeDirectory($path);
        $this->files->put($path, $this->compileStub($stubname));
        $this->scaffoldCommandObj->info('+ ' . $path);

        // Criando o Eloquent
        $path_eloquent = $this->getPath($name, 'repository-eloquent');
        if ($this->files->exists($path_eloquent))
        {
            return $this->scaffoldCommandObj->comment("x " . $path_eloquent);
        }

        $this->makeDirectory($path_eloquent);
        $this->files->put($path_eloquent, $this->compileStub('repository-eloquent'));
        $this->scaffoldCommandObj->info('+ ' . $path_eloquent);
    }

    protected function compileStub($filename)
    {
        $stub = $this->files->get(substr(__DIR__,0, -5) . 'Stubs/'.$filename.'.stub');

        $this->buildStub($this->scaffoldCommandObj->getMeta(), $stub);
        // $this->replaceValidator($stub);

        return $stub;
    }

    protected function bindRepository($model, $repository_name)
    {
        $path = './app/Providers/RepositoryServiceProvider.php';
        $content = $this->files->get($path);

        if (strpos($content, $repository_name) === false) {
            $content = str_replace(
                '//$this->app->bind();',
                "//\$this->app->bind();\n\t\t\$this->app->bind(\App\Repositories\\".$model."Repository::class, \App\Repositories\\".$model."RepositoryEloquent::class);",
                $content
                );
            $this->files->put($path, $content);

            return $this->scaffoldCommandObj->info('+ ' . $path . ' (Atualizado)');
        }

        return $this->scaffoldCommandObj->comment("x " . $path . ' (Ignorado)');
    }
}