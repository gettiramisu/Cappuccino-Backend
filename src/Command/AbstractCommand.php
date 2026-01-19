<?php
declare(strict_types = 1);

//
//  AbstractCommand.php
//  Cappuccino-Backend
//
//  Created by Alexandra Göttlicher
//

namespace Cappuccino\Command;

use Cappuccino\Service\TagMapper;

use Doctrine\ORM\EntityManagerInterface;

use Monolog\Logger;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use RuntimeException;

abstract class AbstractCommand extends Command {
    protected EntityManagerInterface $entityManager;
    protected Filesystem $filesystem;
    protected HttpClientInterface $httpClient;
    protected Logger $logger;
    protected TagMapper $tagMapper;

    /**
     * @internal
     *
     * @param EntityManagerInterface $entityManager
     *
     * @return static
     */
    public function setEntityManager(EntityManagerInterface $entityManager): static {
        $this->entityManager = $entityManager;
        return $this;
    }

    /**
     * @internal
     *
     * @return EntityManagerInterface
     */
    public function getEntityManager(): EntityManagerInterface {
        return $this->entityManager;
    }

    /**
     * @internal
     *
     * @param Filesystem $filesystem
     *
     * @return static
     */
    public function setFilesystem(Filesystem $filesystem): static {
        $this->filesystem = $filesystem;
        return $this;
    }

    /**
     * @internal
     *
     * @return Filesystem
     */
    public function getFilesystem(): Filesystem {
        return $this->filesystem;
    }

    /**
     * @internal
     *
     * @param HttpClientInterface $httpClient
     *
     * @return static
     */
    public function setHttpClient(HttpClientInterface $httpClient): static {
        $this->httpClient = $httpClient;
        return $this;
    }

    /**
     * @internal
     *
     * @return HttpClientInterface
     */
    public function getHttpClient(): HttpClientInterface {
        return $this->httpClient;
    }

    /**
     * @internal
     *
     * @param Logger $logger
     *
     * @return static
     */
    public function setLogger(Logger $logger): static {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @internal
     *
     * @return Logger
     */
    public function getLogger(): Logger {
        return $this->logger;
    }

    /**
     * @internal
     *
     * @return TagMapper $tagMapper
     */
    public function setTagMapper(TagMapper $tagMapper): static {
        $this->tagMapper = $tagMapper;
        return $this;
    }

    /**
     * @return TagMapper
     */
    public function getTagMapper(): TagMapper {
        return $this->tagMapper;
    }

    /**
     * Fetches and parses the latest radio-browser.info JSON export.
     *
     * @return array The parsed JSON
     */
    protected function fetchLatestRadioJsonExport(): array {
        try {
            $response = $this->httpClient->request(method: 'GET', url: 'https://exports.radio-browser.info/radiobrowser_stations_latest.json.gz');
            if (Response::HTTP_OK !== $response->getStatusCode()) {
                throw new RuntimeException(message: "Error downloading the latest radio-browser.info JSON export: {$response->getStatusCode()}");
            }
        } catch (TransportExceptionInterface $exception) {
            throw new RuntimeException(message: "Error downloading the latest radio-browser.info JSON export: {$exception->getMessage()}");
        }

        return json_decode(json: gzdecode(data: $response->getContent()), associative: true);
    }
}
