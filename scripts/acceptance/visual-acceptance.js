'use strict';

const childProcess = require('node:child_process');

const originalExecFileSync = childProcess.execFileSync;
childProcess.execFileSync = function acceptanceExecFileSync(binary, args = [], options = {}) {
    if (binary === 'mariadb' && Array.isArray(args)) {
        const canaryDatabase = process.env.CANARY_DB_DATABASE || '';
        if (!canaryDatabase) {
            throw new Error('CANARY_DB_DATABASE is required for the visual dependency failure probe.');
        }

        args = args.map((argument) => argument === 'DROP TABLE canary.cluster_sessions;'
            ? `DROP TABLE \`${canaryDatabase}\`.cluster_sessions;`
            : argument);
    }

    return originalExecFileSync(binary, args, options);
};

require('./visual-acceptance-core.js');
