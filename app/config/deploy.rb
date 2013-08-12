set :default_stage, "testing"
set :stage_dir,     "app/config/deploy"
require 'capistrano/ext/multistage'

set :application, "rhea"
set :deploy_to,   "/srv/#{application}"
set :app_path,    "app"
set :web_path,    "web"

default_run_options[:pty] = true
set :default_environment, {
  'HTTP_PROXY_REQUEST_FULLURI' => false
}

# Symfony log path
set :log_path,                    app_path + "/logs"

# Symfony cache path
set :cache_path,                  app_path + "/cache"

set :repository,                  "https://github.com/extia/Rhea.git"
# Scm: `accurev`, `bzr`, `cvs`, `darcs`, `subversion`, `mercurial`, `perforce`, or `none`
set :scm,                         :git
set :deploy_via,                  :remote_cache

set :model_manager,               "propel"
# Or: `propel`

# Symfony config file (parameters.(ini|yml|etc...)
set :app_config_file,             "parameters.yml"

set :use_composer,                true
set :composer_options,            "--prefer-dist --verbose -o"
#set :update_vendors,              true
#set :vendors_mode,                "install"

# Use AsseticBundle
set :dump_assetic_assets,         true

# Assets install
set :assets_install,              true
set :assets_symlinks,             true
set :normalize_asset_timestamps,  false

set :shared_files,                [app_path + "/config/parameters.yml"]
set :shared_children,             [log_path]

set :writable_dirs,               [log_path, cache_path]
set :webserver_user,              "www-data"
set :permission_method,           :acl
set :use_set_permissions,         true

set :use_sudo,                    false

set :ssh_options,                 { :forward_agent => true, :compression => false }

set :keep_releases, 3

# Be more verbose by uncommenting the following line
#logger.level = Logger::MAX_LEVEL

# Copy and just update vendors (check http://capifony.org/cookbook/speeding-up-deploy.html)
after 'composer:install', 'symfony:copy_vendors'
after "deploy:restart", "deploy:cleanup"

namespace :symfony do
  desc "Copy vendors from previous release"
  task :copy_vendors, :except => { :no_release => true } do
    pretty_print "--> Copying vendors from previous release"
    run "vendorDir=#{current_path}/vendor; if [ -d $vendorDir ] || [ -h $vendorDir ]; then cp -a $vendorDir #{latest_release}/vendor; fi;"
    puts_ok
  end
  namespace :propel do
    desc "Migrates database to current version"
    task :migrate, :roles => :app, :only => { :primary => true }, :except => { :no_release => true } do
      run "#{try_sudo} sh -c 'cd #{latest_release} && #{php_bin} #{symfony_console} propel:migration:generate-diff --no-ansi'"
      run "#{try_sudo} sh -c 'cd #{latest_release} && #{php_bin} #{symfony_console} propel:migration:migrate --no-ansi'"
    end
  end
end

namespace :deploy do
  desc "Migrates database to current version"
  task :migrate, :roles => :app, :except => { :no_release => true }, :only => { :primary => true } do
    capifony_pretty_print "--> Migrates database to current version"
    if model_manager == "doctrine"
      symfony.doctrine.migrations.migrate
    else
      if model_manager == "propel"
        symfony.propel.migrate
      end
    end
    capifony_puts_ok
  end
end
