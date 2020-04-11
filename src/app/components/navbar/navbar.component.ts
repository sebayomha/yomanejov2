import { Component } from '@angular/core';
import { AuthService } from 'src/app/services/auth/auth.service';
import { Router } from '@angular/router';

@Component({
  selector: 'navbar',
  templateUrl: './navbar.component.html',
  styleUrls: ['./navbar.component.scss']
})

export class NavbarComponent {

  constructor(private authService: AuthService, private router: Router) { }

  ngOnInit() { }

  logout() {
    this.authService.logout(this.authService.decodePayload().idUsuario).subscribe( res => {
      if (res.code == 0) {
        this.router.navigate(['login']);
      }
    });
  }
}