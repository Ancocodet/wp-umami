const { spawnSync } = require('node:child_process');

function runCommand(command, whiteSpaceArg = false) {
    const WP_ENV = process.env.WP_ENV || 'main';

    const container = (WP_ENV === 'test') ? 'tests-cli' : 'cli'

    let args = ['run', 'env', 'run', container, '--', 'wp'].concat(command.split(' '));

    if (whiteSpaceArg) {
        args = args.concat(whiteSpaceArg);
    }

    const result = spawnSync('npm', args);

    if (result.status !== 0) {
        throw `WP-CLI command "${command}" failed: ${result.stderr.toString()}`;
    }

    let output = result.stdout.toString().trim();
    if (output === '') {
        output = result.stderr.toString();
    } else {
        output = output.split('\n').slice(2).join('\n').trim();
    }

    return output;
}

/**
 * Update a WordPress option with a JSON value, handling quoting properly.
 * Passes the JSON value as a separate argument to avoid shell escaping issues.
 *
 * @param {string} optionName - The option name.
 * @param {object} value - The value to set (will be JSON.stringify'd).
 */
function updateOption(optionName, value) {
    const WP_ENV = process.env.WP_ENV || 'main';
    const container = (WP_ENV === 'test') ? 'tests-cli' : 'cli';
    const jsonValue = JSON.stringify(value);

    const args = ['run', 'env', 'run', container, '--', 'wp', 'option', 'update', optionName, jsonValue, '--format=json', '--quiet'];
    const result = spawnSync('npm', args);

    if (result.status !== 0) {
        throw `updateOption("${optionName}") failed: ${result.stderr.toString()}`;
    }
}

/**
 * Get a WordPress option value as parsed JSON.
 *
 * @param {string} optionName - The option name.
 * @returns {object} The parsed option value.
 */
function getOption(optionName) {
    const WP_ENV = process.env.WP_ENV || 'main';
    const container = (WP_ENV === 'test') ? 'tests-cli' : 'cli';

    const args = ['run', 'env', 'run', container, '--', 'wp', 'option', 'get', optionName, '--format=json', '--quiet'];
    const result = spawnSync('npm', args);

    if (result.status !== 0) {
        return undefined;
    }

    let output = result.stdout.toString().trim();
    output = output.split('\n').slice(2).join('\n').trim();
    return JSON.parse(output);
}

module.exports = {
    runCommand,
    updateOption,
    getOption
};
