<?php

namespace Wnx\AlfredEmojiPack\Console;

use DirectoryIterator;
use Ramsey\Uuid\Lazy\LazyUuidFromString;
use Ramsey\Uuid\Uuid;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use ZipArchive;

class GenerateCommand extends Command
{
    protected const PATH_TO_BUILD_DIRECTORY = __DIR__ . '/../../build/';

    protected const ARCHIVE_FILENAME = 'Emoji Pack.alfredsnippets';

    /** @var array[<string>, <string>] */
    protected array $emojiToNames;

    protected function configure()
    {
        $this
            ->setName('generate')
            ->setDescription('Generate Emoji Snippets for Alfred.app');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->clearBuildDirectory();

        $output->writeln('<info>• Creating Snippets …</info>');
        $this->createSnippets();

        $output->writeln('<info>• Creating Achive …</info>');
        $this->createArchive();

        $this->clearBuildDirectory();
        $output->writeln('<info>✓ Done</info>');

        return 0;
    }

    protected function clearBuildDirectory(): void
    {
        $buildDirectory = new DirectoryIterator(self::PATH_TO_BUILD_DIRECTORY);

        /** @var DirectoryIterator $directoryIterator */
        foreach ($buildDirectory as $directoryIterator) {
            if ($directoryIterator->isDot() === false && $directoryIterator->getFileInfo()->getFilename() !== '.gitkeep') {
                unlink($directoryIterator->getPathname());
            }
        }
    }

    /**
     * Create single Snippet files based on an array of Emojis
     * @return void
     */
    protected function createSnippets(): void
    {
        $this->emojiToNames = json_decode(file_get_contents(__DIR__ . '/../../node_modules/gemoji/emoji-to-name.json'), true);

        foreach ($this->getEmojis() as $emoji) {
            $uuid = Uuid::uuid4();

            $snippet = $this->generateSnippet($emoji, $uuid);
            $filename = $this->generateFilename($emoji, $uuid);

            $data = json_encode($snippet, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

            file_put_contents(self::PATH_TO_BUILD_DIRECTORY . $filename, $data);
        }
    }

    /**
     * Get an Array of Emojis which should be turned into Snippets
     * @return array
     */
    protected function getEmojis(): array
    {
        return json_decode(file_get_contents(__DIR__ . '/../../node_modules/gemoji/index.json'), true);
    }

    protected function generateSnippet(array $emoji, LazyUuidFromString $uuid): array
    {
        $emojiCharacter = $emoji['emoji'];
        $names = implode(' ', $emoji['names']);
        $tags = implode(' ', $emoji['tags']);
        $description = $this->emojiToNames[$emojiCharacter];

        return [
            'alfredsnippet' => [
                'snippet' => $emojiCharacter,
                'uuid' => $uuid->toString(),
                'name' => "{$emojiCharacter} {$names}" . ($tags) ?: "- {$tags}",
                'keyword' => ":{$description}:",
            ],
        ];
    }

    /**
     * Create unique Filename for given Emoji
     *
     * @param array $emoji
     * @param LazyUuidFromString $uuid
     * @return string
     */
    protected function generateFilename(array $emoji, LazyUuidFromString $uuid): string
    {
        return "{$emoji['emoji']} - {$uuid->toString()}.json";
    }

    /**
     * Create Snippet Archive
     */
    protected function createArchive(): void
    {
        $rootPath = realpath(self::PATH_TO_BUILD_DIRECTORY);
        $zipArchive = new ZipArchive();
        $zipArchive->open(self::ARCHIVE_FILENAME, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        /** @var SplFileInfo[] $files */
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($rootPath), RecursiveIteratorIterator::LEAVES_ONLY);

        foreach ($files as $name => $file) {
            if (! $file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);

                $zipArchive->addFile($filePath, $relativePath);
            }
        }

        $zipArchive->close();
    }
}
