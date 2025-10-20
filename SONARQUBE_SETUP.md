# SonarQube Setup Guide

This document describes how to run SonarQube code analysis locally for the Firefly III project.

## Overview

This project is configured to use SonarCloud for code quality analysis. The configuration allows you to run analysis locally and view results on the SonarCloud dashboard.

## Prerequisites

Before running the analysis, ensure you have:

1. **Java 11 or later** installed
   ```bash
   java -version
   ```

2. **SonarScanner CLI** installed
   - Download from: https://docs.sonarcloud.io/advanced-setup/ci-based-analysis/sonarscanner-cli/
   - Install instructions:
     ```bash
     cd /tmp
     wget https://binaries.sonarsource.com/Distribution/sonar-scanner-cli/sonar-scanner-cli-6.2.1.4610-linux-x64.zip
     unzip sonar-scanner-cli-6.2.1.4610-linux-x64.zip
     sudo mv sonar-scanner-6.2.1.4610-linux-x64 /opt/sonar-scanner
     sudo ln -s /opt/sonar-scanner/bin/sonar-scanner /usr/local/bin/sonar-scanner
     ```
   - Verify installation:
     ```bash
     sonar-scanner --version
     ```

3. **SonarCloud Token**
   - Obtain your token from: https://sonarcloud.io/account/security
   - Store it securely as an environment variable

## Configuration

The project is configured via `sonar-project.properties`:

- **Project Key**: `samfert-codeium_firefly-iii`
- **Organization**: `samfert-codeium`
- **Host**: `https://sonarcloud.io`
- **Sources**: PHP application code in `app`, `bootstrap`, `database`, `resources`, `routes`, `tests`

## Running Analysis

### Method 1: Using the convenience script

```bash
SONAR_TOKEN=your_token_here ./run-sonar-scanner.sh
```

### Method 2: Direct command

```bash
sonar-scanner \
  -Dsonar.token=your_token_here \
  -Dsonar.projectKey=samfert-codeium_firefly-iii \
  -Dsonar.organization=samfert-codeium \
  -Dsonar.host.url=https://sonarcloud.io
```

## Viewing Results

After the analysis completes, view the results at:
https://sonarcloud.io/dashboard?id=samfert-codeium_firefly-iii

## Troubleshooting

### Java Version Issues

If you get Java version errors, ensure you have Java 11 or later:
```bash
java -version
```

SonarScanner CLI 7.2+ supports Java 11 with JRE auto-provisioning.

### Token Authentication Issues

If you get authentication errors:
1. Verify your token is correct
2. Ensure the token has appropriate permissions for the project
3. Check that the organization name matches: `samfert-codeium`

### Coverage Reports

If you want to include code coverage in the analysis:
1. Run your PHP tests with coverage: `./vendor/bin/phpunit --coverage-clover=coverage.xml`
2. The scanner will automatically pick up `coverage.xml` as configured in `sonar-project.properties`

## Additional Resources

- SonarCloud Documentation: https://docs.sonarcloud.io/
- SonarScanner CLI Documentation: https://docs.sonarcloud.io/advanced-setup/ci-based-analysis/sonarscanner-cli/
- PHP Analysis Documentation: https://docs.sonarsource.com/sonarqube-server/analyzing-source-code/languages/php/
