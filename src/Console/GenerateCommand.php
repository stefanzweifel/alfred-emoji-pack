<?php

namespace Wnx\AlfredEmojiPack\Console;

use DirectoryIterator;
use GuzzleHttp\Client;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Wnx\AlfredEmojiPack\DTOs\Snippet;
use ZipArchive;

class GenerateCommand extends Command
{
    protected const PATH_TO_BUILD_DIRECTORY = __DIR__ . '/../../build/';

    protected const ARCHIVE_FILENAME = 'Emoji Pack.alfredsnippets';

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
        foreach ($this->fetchEmojis() as $emoji) {
            $uuid = sha1(json_encode($emoji));

            $snippet = $this->generateSnippet($emoji, $uuid);
            $filename = $this->generateFilename($emoji, $uuid);

            $data = json_encode($snippet, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

            file_put_contents(self::PATH_TO_BUILD_DIRECTORY . $filename, $data);
        }

        file_put_contents(self::PATH_TO_BUILD_DIRECTORY . 'info.plist', <<<XML
        <?xml version="1.0" encoding="UTF-8"?>
        <!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
        <plist version="1.0">
        <dict>
            <key>snippetkeywordprefix</key>
            <string>:</string>
            <key>snippetkeywordsuffix</key>
            <string>:</string>
        </dict>
        </plist>
        XML);

        copy(__DIR__ . '/../../assets/icon.png', self::PATH_TO_BUILD_DIRECTORY . 'icon.png');
    }

    /**
     * Get an Array of Emojis which should be turned into Snippets
     * @return array
     */
    protected function fetchEmojis(): array
    {
        $client = new Client();

        $originalUrl = 'https://api.github.com/repos/github/gemoji/contents/db/emoji.json';
        $url = 'https://api.github.com/repos/rhysd/gemoji/contents/db/emoji.json?ref=unicode-16.0';

        $response = $client->get($url, [
            'headers' => [
                'Accept' => 'application/vnd.github.v3.raw',
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    }

    protected function generateSnippet(array $emoji, string $uuid): array
    {
        $emojiCharacter = $emoji['emoji'];
        $names = implode(' ', $emoji['aliases']);
        $tags = implode(' ', $emoji['tags']);
        $description = $emoji['description'];

        $names = str_replace('_', ' ', $names);

        return (new Snippet(
            snippet: $emojiCharacter,
            uuid: $uuid,
            name: "{$emojiCharacter} {$names} {$tags}",
            keyword: $description,
        ))->toArray();
    }

    /**
     * Create unique Filename for given Emoji
     *
     * @param array $emoji
     * @param string $uuid
     * @return string
     */
    protected function generateFilename(array $emoji, string $uuid): string
    {
        return "{$emoji['emoji']} - {$uuid}.json";
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
