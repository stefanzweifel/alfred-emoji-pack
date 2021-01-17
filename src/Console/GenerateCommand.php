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
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Wnx\Emoji\Emoji;
use Wnx\Emoji\Parser;
use ZipArchive;

class GenerateCommand extends Command
{
    protected const PATH_TO_DIST_DIRECTORY = __DIR__ . '/../../dist/';

    /** @var string */
    protected const EMOJI_VERSION = '13.1';

    /** @var int */
    protected $now;

    /** @var string */
    protected $deprecationNotice = '# deprecations'.PHP_EOL;

    /** @var \Spatie\Emoji\Generator\Emoji[] */
    protected $emojis;

    /** @var array[] */
    protected $emojisArray;

    /** @var array[] */
    protected $groups;

    /** @var array[] */
    protected $emojiToNames;

    protected function configure()
    {
        $this
            ->setName('generate')
            ->setDescription('Generate the package code from the emoji docs');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->clearDistDirectory();
        
        $this->createSnippets();

        $this->createAlfredSnippetsArchive();
    }

    public function generateSnippet(array $emoji, LazyUuidFromString $uuid): array
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
                'keyword' => ":{$description}:"
            ]
        ];
    }

    public function generateFilename(array $emoji, LazyUuidFromString $uuid): string
    {
        return "{$emoji['emoji']} - {$uuid->toString()}.json";
    }

    /**
     * @param string $emojiCode
     */
    protected function renderEmoji(string $emojiCode): string
    {
        ob_start();
        echo "{$emojiCode}";
        $renderedEmoji = ob_get_contents();
        ob_end_clean();
        return $renderedEmoji;
    }

    protected function clearDistDirectory(): void
    {
        foreach (new DirectoryIterator(self::PATH_TO_DIST_DIRECTORY) as $fileInfo) {
            if (! $fileInfo->isDot()) {
                unlink($fileInfo->getPathname());
            }
        }
    }

    protected function createAlfredSnippetsArchive(): void
    {
        $rootPath = realpath(self::PATH_TO_DIST_DIRECTORY);
        $zipArchive = new ZipArchive();
        $zipArchive->open('Emoji Pack Neo.alfredsnippets', ZipArchive::CREATE | ZipArchive::OVERWRITE);

        // Create recursive directory iterator
        /** @var SplFileInfo[] $files */
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($rootPath), RecursiveIteratorIterator::LEAVES_ONLY);

        foreach ($files as $name => $file) {
            // Skip directories (they would be added automatically)
            if (! $file->isDir()) {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);

                // Add current file to archive
                $zipArchive->addFile($filePath, $relativePath);
            }
        }

        // Zip archive will be created only after closing object
        $zipArchive->close();
    }

    protected function createSnippets(): void
    {
        $encodedEmojiToNames = file_get_contents(__DIR__ . '/../../node_modules/gemoji/emoji-to-name.json');
        $this->emojiToNames = json_decode($encodedEmojiToNames, true);


        $encodedEmojis = file_get_contents(__DIR__ . '/../../node_modules/gemoji/index.json');
        $emojis = json_decode($encodedEmojis, true);


        // Create Snippets
        foreach ($emojis as $emoji) {

            $uuid = Uuid::uuid4();

            $snippet = $this->generateSnippet($emoji, $uuid);
            $filename = $this->generateFilename($emoji, $uuid);

            $data = json_encode($snippet, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

            file_put_contents(__DIR__ . "/../../dist/{$filename}", $data);
        }
    }

}