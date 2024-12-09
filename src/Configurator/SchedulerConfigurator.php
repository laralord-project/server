<?php

namespace Server\Configurator;

class SchedulerConfigurator extends QueueConfigurator implements ConfiguratorContract
{

    public static string $logChannel = 'laralord:scheduler';

    protected array $config = [
        'SERVER_ENV_FILE' => [
            '/secrets/.laralord-env', 'configEnvFile', 'Server configuration .env file'
        ],
        'SERVER_LOG_LEVEL' => [
            "NOTICE", 'logLevel',
            'Log level for server: DEBUG, INFO, NOTICE, WARNING, ERROR, CRITICAL, ALERT, EMERGENCY',
        ],
        'SERVER_WATCH' => [false, 'watch', 'Enable the WATCH mode for development.'],
        'ENV_SOURCE' => [
            'vault', 'envSource', 'The environments variables source, possible values: vault, dir',
        ],
        'ENV_DIR' => ['./.envs', 'envs_dir', "The directory with .env files. Required if ENV_SOURCE is 'dir'"],
        'ENV_VAULT_ADDR' => ["http://vault:8200", 'vault_addr', 'Vault Hashicorp. server addres'],
        'ENV_VAULT_TOKEN' => ["", 'vault_token', 'Vault Hashicorp. tokeb'],
        'ENV_VAULT_STORAGE' => ["secrets", 'vault_storage', 'Vault Hashicorp. KV storage'],
        'ENV_VAULT_PREFIX' => ["", 'vault_prefix', 'Vault Hashicorp. key prefix'],
        'ENV_VAULT_KEY' => ["secrets", 'vault_key', 'Vault Hashicorp. secret key for single mode'],
        'ENV_VAULT_AUTH_TYPE' => ["", 'vault_auth_type', 'Vault Hashicorp. auth type: token, kubernetes'],
        'ENV_VAULT_AUTH_ENDPOINT' => [
            "kubernetes",
            'vault_auth_endpoint',
            'Vault Hashicorp. auth login endpoint. Default: kubernetes',
        ],
        'ENV_VAULT_SA_TOKEN_PATH' => [
            "/var/run/secrets/kubernetes.io/serviceaccount/token",
            'vault_sa_token_path',
            'Vault Hashicorp. Service Account token path. Default: /var/run/secrets/kubernetes.io/serviceaccount/token',
        ],
        'ENV_VAULT_AUTH_ROLE' => ["", 'vault_auth_role', 'Vault Hashicorp. auth role for kubernetes auth'],
        'ENV_VAULT_UPDATE_PERIOD' => [
            "1", 'vault_update_period', 'The period of update the secret in minutes:  0 - update is disabled',
        ],
        'SCHEDULER_WORKERS' => [
            2, 'worker_num',
            'The number of worker processes to start. By default this is set to the number of CPU cores you have.',
        ],
        'APP_BASE_PATH' => ['/var/www', 'basePath', 'Laravel project\'s path'],
        'SYNC_METHOD' => ['redis', 'synchronizer', 'Workers Synchronizer - redis, used for mutex'],
        'SYNC_REDIS_HOST' => ['redis', 'redis_host', 'Redis Host'],
        'SYNC_REDIS_PORT' => ['6379', 'redis_port', 'Redis Port'],
        'SYNC_REDIS_PREFIX' => ['', 'redis_prefix', 'Redis mutex prefix'],
        'SYNC_REDIS_USERNAME' => ['', 'redis_username', 'Redis auth username'],
        'SYNC_REDIS_PASSWORD' => ['', 'redis_password', 'Redis auth password. Leave empty if auth is not required'],
    ];
}
