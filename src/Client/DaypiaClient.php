<?php

declare(strict_types=1);

namespace Dayploy\DaypiaBundle\Client;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class DaypiaClient
{
    final public const CREATE_MEDIAFILE_ENDPOINT = '/v1/machine/createmediafile';
    final public const CREATE_CHAPTER_ENDPOINT = '/v1/machine/createchapter';
    final public const SET_PREVIOUS_CHAPTER_ENDPOINT = '/v1/machine/setpreviouschapter';
    final public const SET_MEDIAFILE_FIRST_CHAPTER_ENDPOINT = '/v1/machine/setmediafilefirstchapter';
    final public const SET_CHAPTER_CONTENT_ENDPOINT = '/v1/machine/setchaptercontent';
    final public const CHUNK_CHAPTER_ENDPOINT = '/v1/machine/chunk-chapter';

    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function createMediafile(
        Uuid $projectId,
        Uuid $mediafileId,
        File $file,
    ): void {
        $this->execute(
            endpoint: self::CREATE_MEDIAFILE_ENDPOINT,
            json: [
                'mediafileId' => (string) $mediafileId,
                'projectId' => (string) $projectId,
                'tagIds' => [],
            ],
            isMultipart: true,
            file: $file,
        );
    }

    public function createChapter(
        Uuid $mediafileId,
        Uuid $chapterId,
        string $name,
    ): void {
        $this->execute(
            endpoint: self::CREATE_CHAPTER_ENDPOINT,
            json: [
                'mediaFileId' => (string) $mediafileId,
                'chapterId' => (string) $chapterId,
                'name' => $name,
            ],
        );
    }

    public function setPreviousChapter(
        Uuid $previousChapterId,
        Uuid $chapterId,
    ): void {
        $this->execute(
            endpoint: self::SET_PREVIOUS_CHAPTER_ENDPOINT,
            json: [
                'previousChapterId' => (string) $previousChapterId,
                'chapterId' => (string) $chapterId,
            ],
        );
    }

    public function setMediaFileFirstChapter(
        Uuid $mediafileId,
        Uuid $chapterId,
    ): void {
        $this->execute(
            endpoint: self::SET_MEDIAFILE_FIRST_CHAPTER_ENDPOINT,
            json: [
                'mediaFileId' => (string) $mediafileId,
                'chapterId' => (string) $chapterId,
            ],
        );
    }

    public function setChapterContent(
        Uuid $chapterId,
        string $breadcrumb,
        string $content,
    ): void {
        $this->execute(
            endpoint: self::SET_CHAPTER_CONTENT_ENDPOINT,
            json: [
                'chapterId' => (string) $chapterId,
                'breadcrumb' => $breadcrumb,
                'content' => $content,
            ],
        );
    }

    public function chunkChapter(
        Uuid $chapterId,
    ): void {
        $this->execute(
            endpoint: self::CHUNK_CHAPTER_ENDPOINT,
            json: [
                'chapterId' => (string) $chapterId,
            ],
        );
    }

    private function execute(
        string $endpoint,
        bool $isMultipart = false,
        ?UploadedFile $file = null,
        string $verb = 'POST',
        ?array $json = null,
    ): ResponseInterface {
        try {
            $headers = [
                'Content-Type' => 'application/json',
            ];

            if ($isMultipart) {
                $headers['Content-Type'] = 'multipart/form-data';
            }

            $options = [
                'headers' => $headers,
            ];

            if ($isMultipart) {
                $fields = [
                    'file' => DataPart::fromPath($file->getRealPath()),
                ];
                if (null !== $json) {
                    $fields = array_merge($fields, $json);
                }

                $formData = new FormDataPart($fields);
                $options['headers'] = array_merge_recursive(
                    $options['headers'],
                    $formData->getPreparedHeaders()->toArray()
                );

                $options['body'] = $formData->bodyToIterable();
            } else {
                if (null !== $json) {
                    $options['json'] = $json;
                }
            }

            $response = $this->client->request(
                method: $verb,
                url: $endpoint,
                options: $options
            );

            // ask headers will throw an exception if the http code is not a 2xx
            $response->getHeaders();

            return $response;
            // @codeCoverageIgnoreStart
        } catch (\Throwable $ex) {
            $this->logger->error(sprintf('The Daypia API call did not work, error: %s', $ex->getMessage()));
            throw new DaypiaException(sprintf('Daypia call failed : %s - %s', $ex->getCode(), $ex->getMessage()));
            // @codeCoverageIgnoreEnd
        }
    }
}
