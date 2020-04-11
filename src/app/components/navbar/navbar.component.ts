import { Component } from '@angular/core';
import { AuthService } from 'src/app/services/auth/auth.service';
import { Router } from '@angular/router';
import { AppSettings } from '../../appConstants';

@Component({
  selector: 'navbar',
  templateUrl: './navbar.component.html',
  styleUrls: ['./navbar.component.scss']
})

export class NavbarComponent {

  USER_ROLE;
  constructor(private authService: AuthService, private router: Router,) { }

  ngOnInit() { 
    this.USER_ROLE = AppSettings.USER_ROLE;
    console.log("asf", this.USER_ROLE)
  }

  logout() {
    this.authService.logout(this.authService.decodePayload().idUsuario).subscribe( res => {
      if (res.code == 0) {
        this.router.navigate(['login']);
      }
    });
  }
}