<?php

namespace Wnx\AlfredEmojiPack\Console;

use Ramsey\Uuid\Lazy\LazyUuidFromString;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Wnx\Emoji\Emoji;
use Wnx\Emoji\Parser;

class GenerateCommand extends Command
{
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
        $encodedEmojiToNames = file_get_contents(__DIR__ . '/../../node_modules/gemoji/emoji-to-name.json');
        $this->emojiToNames = json_decode($encodedEmojiToNames, true);



        $encodedEmojis = file_get_contents(__DIR__. '/../../node_modules/gemoji/index.json');
        $emojis = json_decode($encodedEmojis, true);

        $snippetsCollection = [];

        foreach($emojis as $emoji) {

            $uuid = Uuid::uuid4();

            $snippet = $this->generateSnippet($emoji, $uuid);
            $filename = $this->generateFilename($emoji, $uuid);

            $data = json_encode($snippet, JSON_PRETTY_PRINT);

            $data = mb_convert_encoding($data, 'UTF-8', mb_detect_encoding($data));
            // $data = iconv("ASCII", "UTF-8", $data);

            file_put_contents(__DIR__ . "/../../dist/test1.json", utf8_encode(json_encode($snippet, JSON_PRETTY_PRINT)));
            file_put_contents(__DIR__ . "/../../dist/{$filename}", $data);
            return;
        }
    }

    public function generateSnippet(array $emoji, LazyUuidFromString $uuid)
    {
        $emojiCharacter = $emoji['emoji'];
        $names = implode(' ', $emoji['names']);
        $tags = implode(' ', $emoji['tags']);
        $description = $this->emojiToNames[$emojiCharacter];

        return [
            'alfredsnippet' => [
                'snippet' => utf8_encode($emojiCharacter),
                'uuid' => $uuid->toString(),
                'name' => "{$emojiCharacter} {$names}" . ($tags) ?: "- {$tags}",
                'keyword' => ":{$description}:"
            ]
        ];
    }

    public function generateFilename(array $emoji, LazyUuidFromString $uuid)
    {
        return 'test.json';
        return "{$emoji['emoji']} - {$uuid->toString()}.json";
    }

    /**
     * @return false|string
     */
    protected function renderEmoji(string $emojiCode)
    {
        ob_start();
        echo "{$emojiCode}";
        $renderedEmoji = ob_get_contents();
        ob_end_clean();
        return $renderedEmoji;
    }

}