# Demo Site

This documentation outlines how to run the demo site, showcasing the theme.

## Run the demo site locally

This project also ships with a buildable demo site, which allows developers to preview the base theme with ease. To do so run:

```
make
```

This will build a fully functional Drupal site with the ILO Base Theme enabled by default. After the installation is done visit:

[http://localhost:8080](http://localhost:8080)

Note: the command above builds a demo site as self-contained service. To do so it performs the following commands in the container:

- Run `npm install` to fetch the ILO Design System assets
- Copy all relevant Drupal-related code in `/opt/drupal`
- Build and install the Drupal site

This means that, when fetching a newer version, you might need to rebuild the demo site from scratch. To do so, run:

```
make build-dist
```

## Run the demo site as a Docker service

The demo site is also published in the GitHub Docker registry. To run the site use the following command:

```shell
docker run --rm -p 8082:80 ghcr.io/international-labour-organization/ilo_base_theme:1.x
```

The site will then be available at http://localhost:8082.

In order to run the command above, you need to be authenticated, please check the related [documentation][1].

**Note**: To get the most up-to-date version of `1.x`, make sure to remove any pre-existing images by running the following command:

```shell
docker rmi -f ghcr.io/international-labour-organization/ilo_base_theme:1.x
```

If you need to log onto the container, run:

```shell
docker run -ti --rm ghcr.io/international-labour-organization/ilo_base_theme:1.x bash
```

[1]: https://docs.github.com/en/packages/working-with-a-github-packages-registry/working-with-the-container-registry#authenticating-with-a-personal-access-token-classic
