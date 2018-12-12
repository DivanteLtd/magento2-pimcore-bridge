<?php
/**
 * @package   Divante\PimcoreIntegration
 * @author    Mateusz Bukowski <mbukowski@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Console\Command;

use Divante\PimcoreIntegration\Queue\Processor\ProductQueueProcessor;
use Magento\Framework\App\State;
use Magento\Framework\Registry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ProductImportCommand
 */
class ProductImportCommand extends Command
{
    /**
     * @var ProductQueueProcessor
     */
    private $productQueueProcessor;

    /**
     * @var State
     */
    private $state;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * ProductImport constructor.
     *
     * @param ProductQueueProcessor $productQueueProcessor
     * @param State $state
     * @param Registry $registry
     * @param null $name
     */
    public function __construct(
        ProductQueueProcessor $productQueueProcessor,
        State $state,
        Registry $registry,
        $name = null
    ) {
        parent::__construct($name);

        $this->productQueueProcessor = $productQueueProcessor;
        $this->state = $state;
        $this->registry = $registry;
    }

    /**
     * Configures the current command.a
     *
     * @return void
     */
    public function configure()
    {
        $this->setName('divante:queue-product:process');
        $this->setDescription('Process all new published products from Pimcore');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null
     *
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
        } catch (\Exception $ex) {
            // fail gracefully
        }

        $this->registry->register('isSecureArea', true);

        $start = $this->getCurrentMs();

        $output->writeln('<info>Initialization processing of products queue.</info>');
        $output->writeln(sprintf('<info>Started at %s</info>', (new \DateTime())->format('Y-m-d H:i:s')));
        $output->writeln('Processing...');

        $this->productQueueProcessor->process();

        $end = $this->getCurrentMs();

        $output->writeln(sprintf('<info>Finished at %s</info>', (new \DateTime())->format('Y-m-d H:i:s')));
        $output->writeln(sprintf('<info>Total execution time %sms</info>', $end - $start));

        return 0;
    }

    /**
     *
     * @return float|int
     */
    private function getCurrentMs()
    {
        $mt = explode(' ', microtime());

        return ((int) $mt[1]) * 1000 + ((int) round($mt[0] * 1000));
    }
}
