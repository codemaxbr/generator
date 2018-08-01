<?php

namespace Codemax\Generator\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Input;
use Codemax\Generator\Makes\MakeController;
use Codemax\Generator\Makes\MakeLayout;
use Codemax\Generator\Makes\MakeLocalization;
use Codemax\Generator\Makes\MakeMigration;
use Codemax\Generator\Makes\MakeModel;
use Codemax\Generator\Makes\MakeRoute;
use Codemax\Generator\Makes\MakerTrait;
use Codemax\Generator\Makes\MakeSeed;
use Codemax\Generator\Makes\MakeView;
use Codemax\Generator\Makes\MakeFormRequest;
use Codemax\Generator\Makes\MakeService;
use Codemax\Generator\Makes\MakeRepository;
use Codemax\Generator\Makes\MakeModelObserver;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ScaffoldMakeCommand extends Command
{
    use MakerTrait;

    /**
     * The console command name!
     *
     * @var string
     */
    protected $name = 'make:tudo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '---------------- GERA A PORRA TODA !!! ----------------';

    /**
     * Meta information for the requested migration.
     *
     * @var array
     */
    protected $meta;

    /**
     * @var Composer
     */
    private $composer;

    /**
     * Store name from Model
     *
     * @var string
     */
    private $nameModel = "";

    /**
     * Create a new command instance.
     *
     * @param Filesystem $files
     * @param Composer $composer
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
        $this->composer = app()['composer'];
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $header = "Gerando: {$this->getObjName("Name")}";
        $footer = str_pad('', strlen($header), '-');
        $dump = str_pad('>SUCESSO<', strlen($header), ' ', STR_PAD_BOTH);

        $this->line("\n----------- $header -----------\n");

        $this->makeMeta();
        $this->makeMigration();
        $this->makeSeed();
        $this->makeModel();
        $this->makeController();
        $this->makeFormRequest();
        $this->makeModelObserver();
        $this->makeService();
        $this->makeRepository();
        $this->makeRoute();
        // $this->makeLocalization(); //ToDo - implement in future version
        $this->makeViews();
        $this->makeViewLayout();

        $this->call('migrate');
        $this->call('code:models');

        $this->line("\n----------- $footer -----------");
        $this->comment("----------- $dump -----------");

        $this->composer->dumpAutoloads();

    }

    /**
     * Generate the desired migration.
     *
     * @return void
     */
    protected function makeMeta()
    {
        // ToDo - Verificar utilidade...
        $this->meta['action'] = 'create';
        $this->meta['var_name'] = $this->getObjName("name");
        $this->meta['table'] = $this->getObjName("names");//obsoleto

        $this->meta['ui'] = $this->option('ui');

        $this->meta['namespace'] = $this->getAppNamespace();

        $this->meta['Model'] = $this->getObjName('Name');
        $this->meta['Models'] = $this->getObjName('Names');
        $this->meta['model'] = $this->getObjName('name');
        $this->meta['models'] = $this->getObjName('names');
        $this->meta['ModelMigration'] = "Create{$this->meta['Models']}Table";

        $this->meta['schema'] = $this->option('schema');
        $this->meta['prefix'] = ($prefix = $this->option('prefix')) ? "$prefix." : "";
    }

    /**
     * Generate the desired migration.
     *
     * @return void
     */
    protected function makeMigration()
    {
        new MakeMigration($this, $this->files);
    }

    /**
     * Make a Controller with default actions
     *
     * @return void
     */
    private function makeController()
    {
        new MakeController($this, $this->files);
    }

    /**
     * Make a layout.blade.php with bootstrap
     *
     * @return void
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function makeViewLayout()
    {
        new MakeLayout($this, $this->files);
    }

    /**
     * Generate an Eloquent model, if the user wishes.
     *
     * @return void
     */
    protected function makeModel()
    {
        new MakeModel($this, $this->files);
    }

    /**
     * Generate a Seed
     *
     * @return void
     */
    private function makeSeed()
    {
        new MakeSeed($this, $this->files);
    }

    /**
     * Setup views and assets
     *
     * @return void
     */
    private function makeViews()
    {
        new MakeView($this, $this->files);
    }

    /**
     * Setup views and assets
     *
     * @return void
     */
    private function makeRoute()
    {
        new MakeRoute($this, $this->files);
    }

    /**
     * Setup the localizations
     */
    private function makeLocalization()
    {
        new MakeLocalization($this, $this->files);
    }

    private function makeFormRequest()
    {
        new MakeFormRequest($this, $this->files);
    }

    private function makeModelObserver()
    {
        new MakeModelObserver($this, $this->files);
    }

    private function makeService()
    {
        new MakeService($this, $this->files);
    }

    private function makeRepository()
    {
        new MakeRepository($this, $this->files);
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return
            [
                ['name', InputArgument::REQUIRED, 'The name of the model. (Ex: Post)'],
            ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return
            [
                [
                    'schema',
                    's',
                    InputOption::VALUE_REQUIRED,
                    'Schema to generate scaffold files. (Ex: --schema="title:string")',
                    null
                ],
                [
                    'ui',
                    'ui',
                    InputOption::VALUE_OPTIONAL,
                    'UI Framework to generate scaffold. (Default bs3 - bootstrap 3)',
                    'bs3'
                ],
                [
                    'validator',
                    'a',
                    InputOption::VALUE_OPTIONAL,
                    'Validators to generate scaffold files. (Ex: --validator="title:required")',
                    null
                ],
                [
                    'localization',
                    'l',
                    InputOption::VALUE_OPTIONAL,
                    'Localizations to generate scaffold files. (Ex. --localization="key:value")',
                    null
                ],
                [
                    'lang',
                    'b',
                    InputOption::VALUE_OPTIONAL,
                    'Language for Localization (Ex. --lang="en")',
                    null,
                ],
                [
                    'form',
                    'f',
                    InputOption::VALUE_OPTIONAL,
                    'Use Illumintate/Html Form facade to generate input fields',
                    false
                ],
                [
                    'prefix',
                    'p',
                    InputOption::VALUE_OPTIONAL,
                    'Generate schema with prefix',
                    false
                ]
            ];
    }

    /**
     * Get access to $meta array
     *
     * @return array
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * Generate names
     *
     * @param string $config
     * @return mixed
     * @throws \Exception
     */
    public function getObjName($config = 'Name')
    {
        $names = [];
        $args_name = $this->argument('name');

        // Name[0] = Tweet
        $names['Name'] = str_singular(ucfirst($args_name));
        // Name[1] = Tweets
        $names['Names'] = str_plural(ucfirst($args_name));
        // Name[2] = tweets
        $names['names'] = str_plural(strtolower(preg_replace('/(?<!^)([A-Z])/', '_$1', $args_name)));
        // Name[3] = tweet
        $names['name'] = str_singular(strtolower(preg_replace('/(?<!^)([A-Z])/', '_$1', $args_name)));


        if (!isset($names[$config])) {
            throw new \Exception("Position name is not found");
        };

        return $names[$config];
    }
}
