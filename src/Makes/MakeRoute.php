<?php
/**
 * Created by PhpStorm.
 * User: fernandobritofl
 * Date: 4/22/15
 * Time: 10:34 PM
 */

namespace Codemax\Generator\Makes;

use Illuminate\Filesystem\Filesystem;
use Codemax\Generator\Commands\ScaffoldMakeCommand;
use Codemax\Generator\Migrations\SchemaParser;
use Codemax\Generator\Migrations\SyntaxBuilder;

class MakeRoute
{
    use MakerTrait;

    /**
     * Create a new instance.
     *
     * @param ScaffoldMakeCommand $scaffoldCommand
     * @param Filesystem $files
     * @return void
     */
    public function __construct(ScaffoldMakeCommand $scaffoldCommand, Filesystem $files)
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
        $name = $this->scaffoldCommandObj->getObjName('Name');
        $route_name = floatval(app()::VERSION) < 5.3 ? 'route_old' : 'route';
        $path = $this->getPath($name, $route_name);
        $stub = $this->compileRouteStub();
        
        if (strpos($this->files->get($path), $stub) === false) {
            $this->files->append($path, $this->compileRouteStub());
            return $this->scaffoldCommandObj->info('+ ' . $path . ' (Atualizado)');
        }
        
        return $this->scaffoldCommandObj->comment("x $path" . ' (Ignorado)');
    }

    /**
     * Compile the migration stub.
     *
     * @return string
     */
    protected function compileRouteStub()
    {
        $stub = $this->files->get(substr(__DIR__,0, -5) . 'Stubs/route.stub');

        $this->buildStub($this->scaffoldCommandObj->getMeta(), $stub);

        return $stub;
    }
}