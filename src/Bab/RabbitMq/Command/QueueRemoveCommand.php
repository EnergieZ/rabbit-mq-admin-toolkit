<?php

namespace Bab\RabbitMq\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class QueueRemoveCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('queue:remove')
            ->setDescription('Remove queue of a vhost')
            ->addArgument('vhost', InputArgument::REQUIRED, 'Which vhost should be removed?')
            ->addOption('all', 'a', InputOption::VALUE_NONE, 'Should we remove all queues in vhost?')
            ->addOption('pattern', 'P', InputOption::VALUE_REQUIRED, 'Purge only queues matching pattern', null)
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $pattern = $input->getOption('pattern');
        if (false === $input->getOption('all') && null === $pattern) {
            throw new \InvalidArgumentException('You must use "pattern" or "all" option');
        }
        $vhostManager = $this->getVhostManager($input, $output, $input->getArgument('vhost'));

        // Test pattern
        if (null !== $pattern && false === preg_match($pattern, '')) {
            throw new \InvalidArgumentException(sprintf('Invalid pattern: "%s".', $pattern));
        }

        $hasQueueRemoved = false;
        foreach ($vhostManager->getQueues() as $queue) {
            if (null !== $pattern && 1 !== preg_match($pattern, $queue)) {
                continue;
            }

            $output->writeln(sprintf(
                'Remove queue <comment>%s</comment>.',
                $queue
            ));

            $vhostManager->remove($queue);

            $hasQueueRemoved = true;
        }

        if (false === $hasQueueRemoved) {
            $output->writeln('<info>No queue match the specified pattern</info>.');
        }

        return 0;
    }
}
