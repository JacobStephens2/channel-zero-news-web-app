const path = require('path');
const corePath = '/usr/lib/node_modules/@bubblewrap/cli/node_modules/@bubblewrap/core';
const { TwaManifest } = require(path.join(corePath, 'dist/lib/TwaManifest'));
const { TwaGenerator } = require(path.join(corePath, 'dist/lib/TwaGenerator'));
const { Config } = require(path.join(corePath, 'dist/lib/Config'));
const { JdkHelper } = require(path.join(corePath, 'dist/lib/jdk/JdkHelper'));
const { AndroidSdkTools } = require(path.join(corePath, 'dist/lib/androidSdk/AndroidSdkTools'));
const { GradleWrapper } = require(path.join(corePath, 'dist/lib/GradleWrapper'));
const { ConsoleLog } = require(path.join(corePath, 'dist/lib/Log'));
const { KeyTool } = require(path.join(corePath, 'dist/lib/jdk/KeyTool'));

const PROJECT_DIR = __dirname;
const MANIFEST_FILE = path.join(PROJECT_DIR, 'twa-manifest.json');

async function main() {
  const log = new ConsoleLog('build');

  // Load config
  const config = new Config(
    '/home/jacob/.bubblewrap/jdk/jdk-17.0.11+9',
    '/home/jacob/.bubblewrap/android-sdk'
  );

  // Load TWA manifest
  console.log('Loading TWA manifest...');
  const twaManifest = await TwaManifest.fromFile(MANIFEST_FILE);
  const validationError = twaManifest.validate();
  if (validationError) {
    console.error('Manifest validation error:', validationError);
    process.exit(1);
  }

  // Generate TWA project
  console.log('Generating TWA project...');
  const twaGenerator = new TwaGenerator();
  await twaGenerator.createTwaProject(PROJECT_DIR, twaManifest, log, (progress, total) => {
    process.stdout.write(`\r  Progress: ${progress}/${total}`);
  });
  console.log('\nTWA project generated.');

  // Set up JDK and SDK
  const jdkHelper = new JdkHelper(process, config);
  const androidSdkTools = await AndroidSdkTools.create(process, config, jdkHelper, log);

  // Generate signing keystore if it doesn't exist
  const keystorePath = path.join(PROJECT_DIR, 'android.keystore');
  const fs = require('fs');
  if (!fs.existsSync(keystorePath)) {
    console.log('Generating signing keystore...');
    const keyTool = new KeyTool(jdkHelper, log);
    await keyTool.createSigningKey({
      path: keystorePath,
      alias: 'android',
      keypassword: 'android',
      password: 'android',
      fullName: 'Channel Zero News',
      organizationalUnit: '',
      organization: '',
      country: 'US',
    });
    console.log('Keystore generated.');
  }

  // Build APK
  console.log('Building APK (this may take a minute)...');
  const gradleWrapper = new GradleWrapper(process, androidSdkTools, PROJECT_DIR);
  await gradleWrapper.assembleRelease();
  console.log('Gradle build complete.');

  // Sign the APK
  const unsignedApk = path.join(PROJECT_DIR, 'app', 'build', 'outputs', 'apk', 'release', 'app-release-unsigned.apk');
  const signedApk = path.join(PROJECT_DIR, 'app-release-signed.apk');
  const alignedApk = path.join(PROJECT_DIR, 'app-release-signed-aligned.apk');

  if (fs.existsSync(unsignedApk)) {
    console.log('Signing APK...');
    await androidSdkTools.zipalign(unsignedApk, alignedApk);
    await androidSdkTools.apksigner(
      keystorePath, 'android', 'android', 'android',
      alignedApk, signedApk
    );
    console.log(`\nSigned APK: ${signedApk}`);
  } else {
    // Check for already-signed APK from Gradle
    const releaseApk = path.join(PROJECT_DIR, 'app', 'build', 'outputs', 'apk', 'release', 'app-release.apk');
    if (fs.existsSync(releaseApk)) {
      fs.copyFileSync(releaseApk, signedApk);
      console.log(`\nAPK: ${signedApk}`);
    } else {
      console.log('Looking for APK output...');
      const outputDir = path.join(PROJECT_DIR, 'app', 'build', 'outputs', 'apk', 'release');
      if (fs.existsSync(outputDir)) {
        console.log('Files in output dir:', fs.readdirSync(outputDir));
      } else {
        console.log('Output directory not found. Checking build directory...');
        const buildDir = path.join(PROJECT_DIR, 'app', 'build');
        if (fs.existsSync(buildDir)) {
          const walk = (dir, depth = 0) => {
            if (depth > 3) return;
            for (const f of fs.readdirSync(dir)) {
              const p = path.join(dir, f);
              console.log('  '.repeat(depth) + f);
              if (fs.statSync(p).isDirectory()) walk(p, depth + 1);
            }
          };
          walk(path.join(PROJECT_DIR, 'app', 'build', 'outputs'));
        }
      }
    }
  }
}

main().catch(err => {
  console.error('Build failed:', err);
  process.exit(1);
});
