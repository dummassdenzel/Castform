import { Component, OnDestroy, OnInit } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { RouterOutlet } from '@angular/router';
import { WeatherService } from './services/weather.service';
import { Subscription } from 'rxjs';
import Swal from 'sweetalert2';
import { CommonModule } from '@angular/common';
import { MatTooltipModule } from '@angular/material/tooltip';
import { MatButtonModule } from '@angular/material/button';
import { MatMenuModule } from '@angular/material/menu';
import { RealTimeClockComponent } from "./components/widgets/real-time-clock/real-time-clock.component";

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [RouterOutlet, ReactiveFormsModule, CommonModule, MatTooltipModule, RealTimeClockComponent, MatMenuModule, MatButtonModule],
  templateUrl: './app.component.html',
  styleUrl: './app.component.css'
})
export class AppComponent implements OnInit, OnDestroy {
  title = 'web_app_weather';
  searchForm: any;
  private subscriptions = new Subscription();
  weatherData: any;
  loading: boolean = false;
  metricSystem: string = '&units=metric'; // My default metric system.

  constructor(private builder: FormBuilder, private weather: WeatherService){  
    this.searchForm = builder.group({
      city: ['', Validators.required],
      metricSystem: [this.metricSystem]
    })
  }

  ngOnInit(): void {
    this.searchByLocation();
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }
  
  searchByCity(){
    this.searchForm.patchValue({
      metricSystem: this.metricSystem
    })
    if(!this.searchForm.valid){
      Swal.fire({
        title: "Please enter a City",
        icon: "warning"
      })
      return
    }
    this.subscriptions.add(
      this.weather.searchByCity(this.searchForm.value).subscribe((res:any)=>{
        this.weatherData = res.payload;
      }, error =>{
        switch (error.status){
          case 404:
            Swal.fire({
              title: "City not found",
              text: `${error.error.status.message}`,
              icon: "warning",
              timer: 2000,
              timerProgressBar: true,
            })
          break;
          case 400:
            Swal.fire({
              title: "Invalid City",
              text: `Please enter a valid city.`,
              icon: "warning",
              timer: 2000,
              timerProgressBar: true,
            })
          break;
          default:
            Swal.fire({
              title: "Error fetching data",
              text: `${error.error.status.message}`,
              icon: "error",
              timer: 2000,
              timerProgressBar: true,
            })
        }
      })
    )
  }
  
  searchByLocation(){
    this.loading = true;
    navigator.geolocation.getCurrentPosition((position) => {
      const userLocation = {
        lat: position.coords.latitude,
        lon: position.coords.longitude,
        metricSystem: this.metricSystem
      }      
      this.subscriptions.add(
        this.weather.searchByLocation(userLocation).subscribe((res:any)=>{
          this.weatherData = res.payload;
          console.log(this.weatherData);
          this.loading = false;
        })
      )
    });
  }
  
  changeMetricSystem(metricSystem: string){
    this.metricSystem = metricSystem;
    this.searchByLocation();
  }

  getMetricSymbol(metric: string): string {
    switch(metric){
      case '&units=metric':
        return 'C'
      case '&units=imperial':
          return 'F'
      case '&units=standard':
          return 'K'
      default:
          return ''
    }
  }


  getWindDirection(deg: number): string {
    switch (true) {
        case (deg >= 0 && deg < 22.5):
            return 'N';
        case (deg >= 22.5 && deg < 67.5):
            return 'NE';
        case (deg >= 67.5 && deg < 112.5):
            return 'E';
        case (deg >= 112.5 && deg < 157.5):
            return 'SE';
        case (deg >= 157.5 && deg < 202.5):
            return 'S';
        case (deg >= 202.5 && deg < 247.5):
            return 'SW';
        case (deg >= 247.5 && deg < 292.5):
            return 'W';
        case (deg >= 292.5 && deg < 337.5):
            return 'NW';
        default:
            return 'N';
    }
}


}
