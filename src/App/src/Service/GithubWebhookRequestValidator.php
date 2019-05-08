<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\JenkinsJobRepositoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Validator\AbstractValidator;

final class GithubWebhookRequestValidator extends AbstractValidator
{
    const MISSING_HEADER = 'missingHeader';
    const MISSING_EXTENSION = 'missingExtension';
    const HASH_ALGORITHM_NOT_SUPPORTED = 'hashAlgorithmNotSupported';
    const HOOK_SECRET_MISMATCH = 'hookSecretMismatch';

    protected $messageTemplates = [
        self::MISSING_HEADER => 'Http header \'X-Hub-Signature\' is missing.',
        self::MISSING_EXTENSION => 'Missing \'hash\' extension to check the secret code validity.',
        self::HASH_ALGORITHM_NOT_SUPPORTED => 'Hash algorithm \'$algo\' is not supported.',
        self::HOOK_SECRET_MISMATCH => 'Hook secret does not match.',
    ];

    /**
     * @var JenkinsJobRepositoryInterface
     */
    private $jenkinsJobRepository;

    /**
     * GithubWebhookRequestValidator constructor.
     * @param JenkinsJobRepositoryInterface $jenkinsJobRepository
     * @param array|null $options
     */
    public function __construct(JenkinsJobRepositoryInterface $jenkinsJobRepository, array $options = null)
    {
        $this->jenkinsJobRepository = $jenkinsJobRepository;
        parent::__construct($options);
    }

    /**
     * @param ContainerInterface $container
     * @return GithubWebhookRequestValidator
     */
    public static function fromContainer(ContainerInterface $container): self
    {
        return new self(
            $container->get(JenkinsJobRepositoryInterface::class)
        );
    }

    /**
     * @param ServerRequestInterface  $request
     * @return bool
     * @throws \Exception
     */
    public function isValid($request): bool
    {
        $hookSecret = $this->getHookSecret($request);

        if (empty($request->getHeader('X-Hub-Signature'))) {
            $this->error(self::MISSING_HEADER);
        }

        if (!extension_loaded('hash')) {
            $this->error(self::MISSING_EXTENSION);
        }

        list($algo, $hash) = explode('=', $request->getHeader('X-Hub-Signature')[0], 2) + ['', ''];
        if (!in_array($algo, hash_algos(), TRUE)) {
            $this->error(self::HASH_ALGORITHM_NOT_SUPPORTED);
        }
        $rawPost = file_get_contents('php://input');
        if ($hash !== hash_hmac($algo, $rawPost, $hookSecret)) {
            $this->error(self::HOOK_SECRET_MISMATCH);
        }

        return true;
    }

    /**
     * @param ServerRequestInterface $request
     * @return string
     */
    private function getHookSecret(ServerRequestInterface $request): string
    {
        $input = json_decode($request->getParsedBody()['payload'], true);

        return $this->jenkinsJobRepository->getJobByFullRepositoryName($input['repository']['full_name'])['webhook_secret'];
    }
}
