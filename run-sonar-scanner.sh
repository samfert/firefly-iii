#!/bin/bash
set -e

if [ -z "$SONAR_TOKEN" ]; then
    echo "Error: SONAR_TOKEN environment variable is not set"
    echo "Usage: SONAR_TOKEN=your_token ./run-sonar-scanner.sh"
    exit 1
fi

echo "Running SonarQube analysis..."
echo "Project: samfert-codeium_firefly-iii"
echo "Organization: samfert-codeium"
echo ""

sonar-scanner -Dsonar.token="$SONAR_TOKEN"

echo ""
echo "Analysis complete! View results at:"
echo "https://sonarcloud.io/dashboard?id=samfert-codeium_firefly-iii"
