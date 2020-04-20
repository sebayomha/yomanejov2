import { Component, HostListener } from '@angular/core';
import { Router, NavigationEnd, RouterOutlet } from '@angular/router';
import { slideInAnimation } from './animations';
import { AuthService } from '../app/services/auth/auth.service';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css'],
  animations: [
    slideInAnimation
    // animation triggers go here
  ]
})

export class AppComponent {
  title = 'yoManejo';

  deferredPrompt: any;
  showButton: boolean = false;

  appCanBeInstalled: boolean = false;
  masTardeSelected: boolean = false;

  constructor(private router: Router, public authService: AuthService) {}

  ngOnInit() {
    this.router.events.subscribe((evt) => {
      if (!(evt instanceof NavigationEnd)) {
          return;
      }
      window.scrollTo(0, 0)
    });

    const getMasTardeResult = this.getMasTarde();
    if (getMasTardeResult) {
      if (new Date().getTime() <= Number(getMasTardeResult)) {
        this.masTardeSelected = true;
      }
    } 
  }

  prepareRoute(outlet: RouterOutlet) {
    return outlet && outlet.activatedRouteData && outlet.activatedRouteData['animation'];
  }

  getMasTarde() {
    return localStorage.getItem('later');
  }

  setMasTarde() {
    const now = new Date();
    now.setDate(now.getDate() + 30);
    localStorage.setItem('later', JSON.stringify(now.getTime()));
    this.masTardeSelected = true;
  }

  @HostListener('window:beforeinstallprompt', ['$event'])
    onbeforeinstallprompt(e) {
      this.appCanBeInstalled = true;
      console.log(e);
      // Prevent Chrome 67 and earlier from automatically showing the prompt
      e.preventDefault();
      // Stash the event so it can be triggered later.
      this.deferredPrompt = e;
      this.showButton = true;
    }
    addToHomeScreen() {
    // hide our user interface that shows our A2HS button
    this.showButton = false;
    // Show the prompt
    this.deferredPrompt.prompt();
    // Wait for the user to respond to the prompt
    this.deferredPrompt.userChoice
    .then((choiceResult) => {
    if (choiceResult.outcome === 'accepted') {
      console.log('User accepted the A2HS prompt');
    } else {
      console.log('User dismissed the A2HS prompt');
    }
    this.appCanBeInstalled = false;
    this.deferredPrompt = null;
  });
  }
}


