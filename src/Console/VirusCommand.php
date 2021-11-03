<?php

namespace Inn20\PhpVirus\Console;

use Inn20\PhpVirus\NodeVisitor\ArrayNodeVisitor;
use Inn20\PhpVirus\NodeVisitor\ConstNodeVisitor;
use Inn20\PhpVirus\NodeVisitor\ForeachNodeVisitor;
use Inn20\PhpVirus\NodeVisitor\ForNodeVisitor;
use Inn20\PhpVirus\NodeVisitor\IfNodeVisitor;
use Inn20\PhpVirus\NodeVisitor\MethodCallNodeVisitor;
use Inn20\PhpVirus\NodeVisitor\ParamNodeVisitor;
use Inn20\PhpVirus\NodeVisitor\PropertyFetchNodeVisitor;
use Inn20\PhpVirus\NodeVisitor\StaticVarNodeVisitor;
use Inn20\PhpVirus\NodeVisitor\StringNodeVisitor;
use Inn20\PhpVirus\NodeVisitor\SwitchNodeVisitor;
use Inn20\PhpVirus\NodeVisitor\VariableNodeVisitor;
use Illuminate\Console\Command;
use PhpParser\Error;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NodeConnectingVisitor;
use PhpParser\ParserFactory;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use PhpParser\PrettyPrinter;

class VirusCommand  extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'php-virus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '...';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $loop = (int)$this->ask('loop?', 1);

        $isTest = false;
        if ($this->confirm('test?')) {
            $isTest = true;
        }

        $paths = config('phpvirus.paths');
        foreach ($paths as $path) {
            $this->handlePath($path, $loop, $isTest);
        }
        $this->info("done.");
    }

    protected function handlePath($path, $loop, $isTest)
    {
        $filesystem = new Filesystem();
        $finder = new Finder();

        if (!$filesystem->exists($path)) {
            $this->error("找不到目录 {$path}");
            return;
        }

        if (is_dir($path)) {
            $phpFiles = iterator_to_array($finder->in($path)->name('*.php')->files());
        } else {
            $phpFiles = [$path];
        }

        foreach ($phpFiles as $phpFile) {
            $this->line($phpFile);
            $code = php_strip_whitespace($phpFile);

            for ($i = 0; $i < $loop; $i++) {
                $code = $this->obscure($code);
            }

            if ($isTest) {
                $parsePath = pathinfo($phpFile);
                $phpFile = sprintf('%s%s%s-test.%s', $parsePath['dirname'], DIRECTORY_SEPARATOR, $parsePath['filename'], $parsePath['extension']);
            }
            file_put_contents($phpFile, $code);
        }

        $this->info("[{$path}] done.");
    }

    private function obscure($code)
    {

        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        try {
            $ast = $parser->parse($code);
        } catch (Error $error) {
            return $code;
        }

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NodeConnectingVisitor());
        $traverser->addVisitor(new ConstNodeVisitor());
        $traverser->addVisitor(new StaticVarNodeVisitor());
        $traverser->addVisitor(new ParamNodeVisitor());
        $traverser->addVisitor(new ForeachNodeVisitor());
        $traverser->addVisitor(new ForNodeVisitor());
        $traverser->addVisitor(new IfNodeVisitor());
        $traverser->addVisitor(new ArrayNodeVisitor());
        $traverser->addVisitor(new SwitchNodeVisitor());
        $traverser->addVisitor(new MethodCallNodeVisitor());
        $traverser->addVisitor(new PropertyFetchNodeVisitor());
        //$traverser->addVisitor(new FunctionNodeVisitor());
        $traverser->addVisitor(new StringNodeVisitor());
        //$traverser->addVisitor(new ConstFetchNodeVisitor());
        $traverser->addVisitor(new VariableNodeVisitor());
        ////x$traverser->addVisitor(new LineNodeVisitor());
        $ast = $traverser->traverse($ast);

        $prettyPrinter = new PrettyPrinter\Standard;
        return $prettyPrinter->prettyPrintFile($ast);
    }

}
