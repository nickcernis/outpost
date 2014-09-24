Vagrant.configure('2') do |config|
  config.vm.box = 'nickcernis/outpost'

  # Forward ports. (Used for debugging.)
  #config.vm.network :forwarded_port, guest: 80, host: 8080, auto_correct: true
  #config.vm.network :forwarded_port, guest: 3306, host: 3333, auto_correct: true

  # Give the virtual machine a static IP
  config.vm.network :private_network, ip: '192.168.53.53' # The A record for my.outpost.rocks points to this IP address

  # Sync local wp-content folder to Apache server root at /var/www/html/wp-content/
  # Outpost now symlinks var/www/html/wp-content to /vagrant/wp/wp-content instead of creating an additional share
  # config.vm.synced_folder 'wp/wp-content/', '/var/www/html/wp-content', :create => 'true', :owner => 'vagrant', :group => 'www-data', :mount_options => ['dmode=775,fmode=664']

  config.vm.provision :shell, :path => 'vagrant/wp-install.sh'

  # TODO: Read config file to determine nginx or Apache, PHP version, WP version, and mysqldump frequency
end