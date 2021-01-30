INSERT INTO `settings` (`settings_key`, `settings_value`)
VALUES ('foodorder/backend-icon', 'fas fa-shopping-cart');

INSERT INTO `permission` (`permission_key`, `module`, `label`, `nav_label`, `nav_href`, `show_in_menu`, `needs_globaladmin`) VALUES ('toggleshop', 'OnePlace\\Foodorder\\Controller\\ApiController', 'Shop öffnen/schliessen', '', '', '0', '0');

COMMIT;
