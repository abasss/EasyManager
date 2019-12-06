#---------------------------------------------------------
# Spec file to build a rpm file
#
# This is an example to build a rpm file. You can use this 
# file to build a package for your own distributions and 
# edit it if you need to match your rules.
# --------------------------------------------------------

Name: mounir
Version: __VERSION__
Release: __RELEASE__
Summary: ERP and CRM software for small and medium companies or foundations 
Summary(es): Software ERP y CRM para pequeñas y medianas empresas, asociaciones o autónomos
Summary(fr): Logiciel ERP & CRM de gestion de PME/PMI, auto-entrepreneurs ou associations
Summary(it): Programmo gestionale per piccole imprese, fondazioni e liberi professionisti

License: GPL-3.0+
#Packager: Laurent Destailleur (Eldy) <eldy@users.sourceforge.net>
Vendor: ERP dev team

URL: https://www.google.com
Source0: https://www.google.com/files/lastbuild/package_rpm_opensuse/%{name}-%{version}.tgz
Patch0: %{name}-forrpm.patch
BuildArch: noarch
BuildRoot: %{_tmppath}/%{name}-%{version}-build

Group: Productivity/Office/Management
Requires: apache2, apache2-mod_php5, php5 >= 5.3.0, php5-gd, php5-ldap, php5-imap, php5-mysql, php5-openssl, dejavu
Requires: mysql-community-server, mysql-community-server-client 
%if 0%{?suse_version}
BuildRequires: update-desktop-files fdupes
%endif

# Set yes to build test package, no for release (this disable need of /usr/bin/php not found by OpenSuse)
AutoReqProv: no


%description
An easy to use CRM & ERP open source/free software package for small  
and medium companies, foundations or freelances. It includes different 
features for Enterprise Resource Planning (ERP) and Customer Relationship 
Management (CRM) but also for different other activities.
ERP was designed to provide only features you need and be easy to 
use.

%description -l es
Un software ERP y CRM para pequeñas y medianas empresas, asociaciones
o autónomos. Incluye diferentes funcionalidades para la Planificación 
de Recursos Empresariales (ERP) y Gestión de la Relación con los
Clientes (CRM) así como para para otras diferentes actividades. 
ERP ha sido diseñado para suministrarle solamente las funcionalidades
que necesita y haciendo hincapié en su facilidad de uso.
    
%description -l fr
Logiciel ERP & CRM de gestion de PME/PMI, autoentrepreneurs, 
artisans ou associations. Il permet de gérer vos clients, prospect, 
fournisseurs, devis, factures, comptes bancaires, agenda, campagnes mailings
et bien d'autres choses dans une interface pensée pour la simplicité.

%description -l it
Un programmo gestionale per piccole e medie
imprese, fondazioni e liberi professionisti. Include varie funzionalità per
Enterprise Resource Planning e gestione dei clienti (CRM), ma anche ulteriori
attività. Progettato per poter fornire solo ciò di cui hai bisogno 
ed essere facile da usare.
Programmo web, progettato per poter fornire solo ciò di 
cui hai bisogno ed essere facile da usare.



#---- prep
%prep
%setup -q
%patch0 -p0 -b .patch


#---- build
%build
# Nothing to build


#---- install
%install
%{__rm} -rf $RPM_BUILD_ROOT

%{__mkdir} -p $RPM_BUILD_ROOT%{_sysconfdir}/%{name}
%{__install} -m 644 build/rpm/conf.php $RPM_BUILD_ROOT%{_sysconfdir}/%{name}/conf.php
%{__install} -m 644 build/rpm/httpd-mounir.conf $RPM_BUILD_ROOT%{_sysconfdir}/%{name}/apache.conf
%{__install} -m 644 build/rpm/file_contexts.mounir $RPM_BUILD_ROOT%{_sysconfdir}/%{name}/file_contexts.mounir
%{__install} -m 644 build/rpm/install.forced.php.opensuse $RPM_BUILD_ROOT%{_sysconfdir}/%{name}/install.forced.php

%{__mkdir} -p $RPM_BUILD_ROOT%{_datadir}/pixmaps
%{__install} -m 644 doc/images/mounir_48x48.png $RPM_BUILD_ROOT%{_datadir}/pixmaps/%{name}.png
%{__mkdir} -p $RPM_BUILD_ROOT%{_datadir}/applications
#desktop-file-install --delete-original --dir=$RPM_BUILD_ROOT%{_datadir}/applications build/rpm/%{name}.desktop
%{__install} -m 644 build/rpm/mounir.desktop $RPM_BUILD_ROOT%{_datadir}/applications/%{name}.desktop

%{__mkdir} -p $RPM_BUILD_ROOT%{_datadir}/%{name}/build/rpm
%{__mkdir} -p $RPM_BUILD_ROOT%{_datadir}/%{name}/build/tgz
%{__mkdir} -p $RPM_BUILD_ROOT%{_datadir}/%{name}/htdocs
%{__mkdir} -p $RPM_BUILD_ROOT%{_datadir}/%{name}/scripts
%{__cp} -pr build/rpm/*     $RPM_BUILD_ROOT%{_datadir}/%{name}/build/rpm
%{__cp} -pr build/tgz/*     $RPM_BUILD_ROOT%{_datadir}/%{name}/build/tgz
%{__cp} -pr htdocs  $RPM_BUILD_ROOT%{_datadir}/%{name}
%{__cp} -pr scripts $RPM_BUILD_ROOT%{_datadir}/%{name}
%{__rm} -rf $RPM_BUILD_ROOT%{_datadir}/%{name}/htdocs/includes/ckeditor/_source  
%{__rm} -rf $RPM_BUILD_ROOT%{_datadir}/%{name}/htdocs/includes/fonts

# Lang
echo "%defattr(0644, root, root, 0755)" > %{name}.lang
echo "%dir %{_datadir}/%{name}/htdocs/langs" >> %{name}.lang
for i in $RPM_BUILD_ROOT%{_datadir}/%{name}/htdocs/langs/*_*
do
  lang=$(basename $i)
  lang1=`expr substr $lang 1 2`; 
  lang2=`expr substr $lang 4 2 | tr "[:upper:]" "[:lower:]"`; 
  echo "%dir %{_datadir}/%{name}/htdocs/langs/${lang}" >> %{name}.lang
  if [ "$lang1" = "$lang2" ] ; then
    echo "%lang(${lang1}) %{_datadir}/%{name}/htdocs/langs/${lang}/*.lang"
  else
    echo "%lang(${lang}) %{_datadir}/%{name}/htdocs/langs/${lang}/*.lang"
  fi
done >>%{name}.lang

%if 0%{?suse_version}

# Enable this command to tag desktop file for suse
%suse_update_desktop_file mounir

# Enable this command to allow suse detection of duplicate files and create hardlinks instead
%fdupes $RPM_BUILD_ROOT%{_datadir}/%{name}/htdocs

%endif


#---- clean
%clean
%{__rm} -rf $RPM_BUILD_ROOT



#---- files
%files -f %{name}.lang

%defattr(0755, root, root, 0755)

%dir %_datadir/mounir

%dir %_datadir/mounir/scripts
%_datadir/mounir/scripts/*

%defattr(-, root, root, 0755)
%doc COPYING ChangeLog doc/index.html htdocs/langs/HOWTO-Translation.txt

%_datadir/pixmaps/mounir.png
%_datadir/applications/mounir.desktop

%dir %_datadir/mounir/build

%dir %_datadir/mounir/build/rpm
%_datadir/mounir/build/rpm/*

%dir %_datadir/mounir/build/tgz
%_datadir/mounir/build/tgz/*

%dir %_datadir/mounir/htdocs
%_datadir/mounir/htdocs/accountancy
%_datadir/mounir/htdocs/adherents
%_datadir/mounir/htdocs/admin
%_datadir/mounir/htdocs/api
%_datadir/mounir/htdocs/asset
%_datadir/mounir/htdocs/asterisk
%_datadir/mounir/htdocs/barcode
%_datadir/mounir/htdocs/blockedlog
%_datadir/mounir/htdocs/bookmarks
%_datadir/mounir/htdocs/bom
%_datadir/mounir/htdocs/cashdesk
%_datadir/mounir/htdocs/categories
%_datadir/mounir/htdocs/collab
%_datadir/mounir/htdocs/comm
%_datadir/mounir/htdocs/commande
%_datadir/mounir/htdocs/compta
%_datadir/mounir/htdocs/conf
%_datadir/mounir/htdocs/contact
%_datadir/mounir/htdocs/contrat
%_datadir/mounir/htdocs/core
%_datadir/mounir/htdocs/cron
%_datadir/mounir/htdocs/custom
%_datadir/mounir/htdocs/datapolicy
%_datadir/mounir/htdocs/dav
%_datadir/mounir/htdocs/debugbar
%_datadir/mounir/htdocs/don
%_datadir/mounir/htdocs/ecm
%_datadir/mounir/htdocs/emailcollector
%_datadir/mounir/htdocs/expedition
%_datadir/mounir/htdocs/expensereport
%_datadir/mounir/htdocs/exports
%_datadir/mounir/htdocs/externalsite
%_datadir/mounir/htdocs/fichinter
%_datadir/mounir/htdocs/fourn
%_datadir/mounir/htdocs/ftp
%_datadir/mounir/htdocs/holiday
%_datadir/mounir/htdocs/hrm
%_datadir/mounir/htdocs/imports
%_datadir/mounir/htdocs/includes
%_datadir/mounir/htdocs/install
%_datadir/mounir/htdocs/langs/HOWTO-Translation.txt
%_datadir/mounir/htdocs/livraison
%_datadir/mounir/htdocs/loan
%_datadir/mounir/htdocs/mailmanspip
%_datadir/mounir/htdocs/margin
%_datadir/mounir/htdocs/modulebuilder
%_datadir/mounir/htdocs/mrp
%_datadir/mounir/htdocs/multicurrency
%_datadir/mounir/htdocs/opensurvey
%_datadir/mounir/htdocs/paybox
%_datadir/mounir/htdocs/paypal
%_datadir/mounir/htdocs/printing
%_datadir/mounir/htdocs/product
%_datadir/mounir/htdocs/projet
%_datadir/mounir/htdocs/public
%_datadir/mounir/htdocs/reception
%_datadir/mounir/htdocs/resource
%_datadir/mounir/htdocs/societe
%_datadir/mounir/htdocs/stripe
%_datadir/mounir/htdocs/supplier_proposal
%_datadir/mounir/htdocs/support
%_datadir/mounir/htdocs/theme
%_datadir/mounir/htdocs/takepos
%_datadir/mounir/htdocs/ticket
%_datadir/mounir/htdocs/user
%_datadir/mounir/htdocs/variants
%_datadir/mounir/htdocs/webservices
%_datadir/mounir/htdocs/website
%_datadir/mounir/htdocs/*.ico
%_datadir/mounir/htdocs/*.patch
%_datadir/mounir/htdocs/*.php
%_datadir/mounir/htdocs/*.txt

%dir %{_sysconfdir}/mounir

%defattr(0664, root, www)
%config(noreplace) %{_sysconfdir}/mounir/conf.php
%config(noreplace) %{_sysconfdir}/mounir/apache.conf
%config(noreplace) %{_sysconfdir}/mounir/install.forced.php
%config(noreplace) %{_sysconfdir}/mounir/file_contexts.mounir



#---- post (after unzip during install)
%post

echo Run post script of packager mounir_opensuse.spec

# Define vars
export docdir="/var/lib/mounir/documents"
export apachelink="%{_sysconfdir}/apache2/conf.d/mounir.conf"
export apacheuser='wwwrun';
export apachegroup='www';

# Remove mounir install/upgrade lock file if it exists
%{__rm} -f $docdir/install.lock

# Create empty directory for uploaded files and generated documents 
echo Create document directory $docdir
%{__mkdir} -p $docdir

# Set correct owner on config files
%{__chown} -R root:$apachegroup /etc/mounir/*

# If a conf already exists and its content was already completed by installer
export config=%{_sysconfdir}/mounir/conf.php
if [ -s $config ] && grep -q "File generated by" $config
then 
  # File already exist. We add params not found.
  echo Add new params to overwrite path to use shared libraries/fonts
  grep -q -c "mounir_lib_ADODB_PATH" $config     || [ ! -d "/usr/share/php/adodb" ]  || echo "<?php \$mounir_lib_ADODB_PATH='/usr/share/php/adodb'; ?>" >> $config
  grep -q -c "mounir_lib_FPDI_PATH" $config      || [ ! -d "/usr/share/php/fpdi" ]   || echo "<?php \$mounir_lib_FPDI_PATH='/usr/share/php/fpdi'; ?>" >> $config
  #grep -q -c "mounir_lib_GEOIP_PATH" $config    || echo "<?php \$mounir_lib_GEOIP_PATH=''; ?>" >> $config
  grep -q -c "mounir_lib_NUSOAP_PATH" $config    || [ ! -d "/usr/share/php/nusoap" ] || echo "<?php \$mounir_lib_NUSOAP_PATH='/usr/share/php/nusoap'; ?>" >> $config
  grep -q -c "mounir_lib_ODTPHP_PATHTOPCLZIP" $config || [ ! -d "/usr/share/php/libphp-pclzip" ]  || echo "<?php \$mounir_lib_ODTPHP_PATHTOPCLZIP='/usr/share/php/libphp-pclzip'; ?>" >> $config
  #grep -q -c "mounir_lib_PHPEXCEL_PATH" $config || echo "<?php \$mounir_lib_PHPEXCEL_PATH=''; ?>" >> $config
  #grep -q -c "mounir_lib_TCPDF_PATH" $config    || echo "<?php \$mounir_lib_TCPDF_PATH=''; ?>" >> $config
  grep -q -c "mounir_js_CKEDITOR" $config        || [ ! -d "/usr/share/javascript/ckeditor" ]  || echo "<?php \$mounir_js_CKEDITOR='/javascript/ckeditor'; ?>" >> $config
  grep -q -c "mounir_js_JQUERY" $config          || [ ! -d "/usr/share/javascript/jquery" ]    || echo "<?php \$mounir_js_JQUERY='/javascript/jquery'; ?>" >> $config
  grep -q -c "mounir_js_JQUERY_UI" $config       || [ ! -d "/usr/share/javascript/jquery-ui" ] || echo "<?php \$mounir_js_JQUERY_UI='/javascript/jquery-ui'; ?>" >> $config
  grep -q -c "mounir_js_JQUERY_FLOT" $config     || [ ! -d "/usr/share/javascript/flot" ]      || echo "<?php \$mounir_js_JQUERY_FLOT='/javascript/flot'; ?>" >> $config
  grep -q -c "mounir_font_DOL_DEFAULT_TTF_BOLD" $config || echo "<?php \$mounir_font_DOL_DEFAULT_TTF_BOLD='/usr/share/fonts/truetype/DejaVuSans-Bold.ttf'; ?>" >> $config      
fi

# Create a config link mounir.conf
if [ ! -L $apachelink ]; then
  apachelinkdir=`dirname $apachelink`
  if [ -d $apachelinkdir ]; then
    echo Create mounir web server config link from %{_sysconfdir}/mounir/apache.conf to $apachelink
    ln -fs %{_sysconfdir}/mounir/apache.conf $apachelink
  else
    echo Do not create link $apachelink - web server conf dir $apachelinkdir not found. web server package may not be installed
  fi
fi

echo Set permission to $apacheuser:$apachegroup on /var/lib/mounir
%{__chown} -R $apacheuser:$apachegroup /var/lib/mounir
%{__chmod} -R o-w /var/lib/mounir

# Restart web server
echo Restart web server
if [ -f %{_sysconfdir}/init.d/httpd ]; then
  %{_sysconfdir}/init.d/httpd restart
fi
if [ -f %{_sysconfdir}/init.d/apache2 ]; then
  %{_sysconfdir}/init.d/apache2 restart
fi

# Restart mysql
echo Restart mysql
if [ -f /etc/init.d/mysqld ]; then
  /sbin/service mysqld restart
fi
if [ -f /etc/init.d/mysql ]; then
  /sbin/service mysql restart
fi

# Show result
echo
echo "----- ERP %version-%release - (c) ERP dev team -----"
echo "ERP files are now installed (into /usr/share/mounir)."
echo "To finish installation and use ERP, click on the menu" 
echo "entry ERP ERP-CRM or call the following page from your"
echo "web browser:"  
echo "http://localhost/mounir/"
echo "-------------------------------------------------------"
echo


#---- postun (after upgrade or uninstall)
%postun

if [ "x$1" = "x0" ] ;
then
  # Remove
  echo "Removed package"
  
  # Define vars
  export apachelink="%{_sysconfdir}/apache2/conf.d/mounir.conf"
  
  # Remove apache link
  if [ -L $apachelink ] ;
  then
    echo "Delete apache config link for ERP ($apachelink)"
    %{__rm} -f $apachelink
    status=purge
  fi
  
  # Restart web servers if required
  if [ "x$status" = "xpurge" ] ;
  then
    # Restart web server
    echo Restart web server
    if [ -f %{_sysconfdir}/init.d/httpd ]; then
      %{_sysconfdir}/init.d/httpd restart
    fi
    if [ -f %{_sysconfdir}/init.d/apache2 ]; then
      %{_sysconfdir}/init.d/apache2 restart
    fi
  fi
else
  # Upgrade
  echo "No remove action done (this is an upgrade)"
fi


# version x.y.z-0.1.a for alpha, x.y.z-0.2.b for beta, x.y.z-0.3 for release
%changelog
__CHANGELOGSTRING__
