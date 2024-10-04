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

    return result.stdout.toString();
}

module.exports = {
    runCommand
};
