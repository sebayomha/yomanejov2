import { Component } from '@angular/core';
import { TestService } from './services/testing/test.service';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})

export class AppComponent {
  title = 'yoManejo';

  constructor(private testService: TestService) {}

  probarServicios() {
   this.testService.getCronograma('papa', 'papa2').subscribe( (response) => {
      console.log(response);
    });
  }
}


