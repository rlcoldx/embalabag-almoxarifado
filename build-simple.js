#!/usr/bin/env node

// Script ultra-simples para build sem configurações complexas
const { execSync } = require('child_process');
const path = require('path');

console.log('🚀 Iniciando build ultra-simples...');

try {
    // Comando webpack com configuração inline mínima e opções válidas
    const webpackCommand = [
        'npx',
        '--node-options="--max-old-space-size=4096"',
        'webpack',
        '--mode=production',
        '--entry=./view/assets/js/init.js',
        '--output-path=./view/assets/dist',
        '--output-filename=main.js',
        '--output-public-path=/view/assets/dist/',
        '--no-devtool',
        '--env', 'NODE_ENV=erp'
    ].join(' ');

    console.log('📦 Executando webpack com configuração inline...');
    console.log('🔧 Comando:', webpackCommand);
    
    execSync(webpackCommand, { stdio: 'inherit', shell: true });
    
    console.log('✅ Build concluído com sucesso!');
} catch (error) {
    console.error('❌ Erro no build:', error.message);
    process.exit(1);
}
