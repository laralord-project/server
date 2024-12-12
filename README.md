
[![Test](https://github.com/laralord-project/server/actions/workflows/test.yaml/badge.svg?branch=main)](https://github.com/laralord-project/server/actions/workflows/test.yaml)


# <img src="assets/logo.png" width="28" alt="LaraLord Server" >  &nbsp; Laralord Project - Server

Laralord enables multi-tenancy for any stateless Laravel application without requiring code updates.
It is an OpenSwoole wrapper designed to provide a high-performance, multi-tenant server setup for Laravel Application.

---

## Key Features

1. **Multi-Tenant Support**:
    - Detect tenant ID via headers, cookies, JWT tokens, query strings, or POST data.
    - Operate in:
        - **Single-Tenant Mode**: Optimized server for a single tenant with pre-booted Laravel instances for ultra-fast
          responses.
        - **Multi-Tenant Mode**: Dynamically resolve environment credentials per tenant, supporting concurrent requests
          with isolated environments.
        - **S3 Bucket Proxy**: Simplifies serving static files and tenant-specific frontends from an S3 or S3-compatible
          bucket.

2. **High Performance**:
    - Pre-boot Laravel application in **Single-Tenant Mode**, reducing response delay by up to 20ms.
    - **Process Isolation**: Each request is executed in a separate process using the `pcntl` extension.

3. **Unified Queue Workers and Scheduler**:
    - Fair distribution of resources between tenants in both the queue system and scheduler.
    - Supports isolated task execution per tenant, ensuring that each tenant has equal access to resources.

4. **Dynamic Credentials Update**:
    - Periodically fetch and update credentials from HashiCorp Vault, ensuring that tenant environments are always using
      the latest credentials.

5. **Containerization Support**:
    - Application built for containerization, offering simple and clear configuration, even through environment
      variables.

---

## Requirements

- **PHP**: >= 8.2
- **PHP Extensions**: `openswoole`, `inotify`, `apcu`, `sysvmsg`, `pcntl`, `redis`
- **Environment Variables Source**: Vault, file-based, or directory-based configurations supported
- **System Utilities**: `bash`, `curl`

---

## **Documentation**

1. **[Installation](documentation/01_installation.md)**
    - [Composer](documentation/01_installation.md#composer-install) 
    - [Download Binary File](documentation/01_installation.md#d)
    - [Docker Image](documentation/01_installation.md#docker-image)
2. **[Server](documentation/02_server.md)**
    - [How it works...](documentation/02_server.md#how-it-works)
    - [Commands](documentation/02_server.md#commands)
    - [Configuration](documentation/02_server.md#configuration)
3. **[S3-Proxy](documentation/03_s3-proxy.md)**
    - [How it works...](documentation/03_s3-proxy.md#how-it-works)
    - [Commands](documentation/03_s3-proxy.md#commands)
    - [Configuration](documentation/03_s3-proxy.md#configuration)
4. **[Queue](documentation/04_queue.md)**
   - [How it works...](documentation/04_queue.md#how-it-works)
   - [Commands](documentation/04_queue.md#commands)
   - [Configuration](documentation/04_queue.md#configuration)
5. **[Scheduler](documentation/05_scheduler.md)**
    - [How it works...](documentation/05_scheduler.md#how-it-works)
    - [Commands](documentation/05_scheduler.md#commands)
    - [Configuration](documentation/05_scheduler.md#configuration)
6. **[Environment Source and Resolvers](documentation/06_environment-source-tenant-resolver.md)**
7. **[Multi-Tenant Helpers](documentation/07_helpers.md)**

---

## Licensing

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

