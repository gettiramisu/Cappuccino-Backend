<?php
declare(strict_types = 1);

//
//  DebugTagsCommand.php
//  Cappuccino-Backend
//
//  Created by Alexandra Göttlicher
//

namespace Cappuccino\Command;

use Cappuccino\DTO\RadioDTO;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class DebugTagsCommand extends AbstractCommand {
    public function __construct() {
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure(): void {
        $this->setName(name: 'cappuccino:debug:tags')
             ->setDescription(description: 'Displays all tags from the latest radio-browser.info JSON export with their mapped tag.')
             ->addOption(name: 'mapped', shortcut: 'm', mode: InputOption::VALUE_NONE, description: 'Only display tags with a mapping.')
             ->addOption(name: 'unmapped', shortcut: 'u', mode: InputOption::VALUE_NONE, description: 'Only display tags without a mapping.')
             ->addOption(name: 'save', shortcut: 's', mode: InputOption::VALUE_NONE, description: 'Save the output to a .txt file in the current working directory.');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int {
        $onlyMapped = $input->getOption(name: 'mapped');
        $onlyUnmapped = $input->getOption(name: 'unmapped');
        $save = $input->getOption(name: 'save');

        $radioJson = $this->fetchLatestRadioJsonExport();

        // Get all tags, remove duplicates and sort them.
        $tags = [];
        foreach ($radioJson as $json) {
            $radio = RadioDTO::fromArray(array: $json);
            $tags = array_merge(array: $tags, arrays: $radio->tags);
        }
        $tags = array_unique(array: $tags);
        sort(array: $tags);

        // Map each tag and print it out.
        $lines = [];
        foreach ($tags as $tag) {
            $mapped = $this->tagMapper->map(rawTag: $tag);

            if ($onlyMapped && !$mapped) {
                continue;
            }
            if ($onlyUnmapped && $mapped) {
                continue;
            }

            $line = "$tag => $mapped";
            $lines[] = $line;
            $output->writeln(messages: $line);
        }

        if ($save) {
            file_put_contents(filename: getcwd() . '/debug_tags.txt', data: implode("\n", $lines));
        }

        return Command::SUCCESS;
    }
}
